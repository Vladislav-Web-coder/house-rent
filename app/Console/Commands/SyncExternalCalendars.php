<?php

namespace App\Console\Commands;

use App\Models\CalendarLink;
use App\Models\SyncedBooking;
use App\Services\CacheService;
use ICal\ICal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncExternalCalendars extends Command
{
    protected $signature = 'calendar:sync-external {--property= : ID конкретного объекта}';
    protected $description = 'Синхронизация внешних календарей (Авито, Суточно и т.д.)';

    public function __construct(
        protected CacheService $cacheService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Начинаем синхронизацию внешних календарей...');

        // Помечаем, что идёт импорт (защита от гонок)
        $this->cacheService->markIcalImportStarted();

        $affectedPropertyIds = [];
        $totalStats = ['imported' => 0, 'updated' => 0, 'deleted' => 0];

        try {
            $query = CalendarLink::query();

            // Если указан конкретный объект — синхронизируем только его
            if ($propertyId = $this->option('property')) {
                $query->where('property_id', $propertyId);
            }

            $links = $query->get();
            $this->info("Найдено {$links->count()} ссылок для синхронизации");

            // Отключаем Observers на время импорта
            // Чтобы не сбрасывать кэш на каждой итерации
            SyncedBooking::unsetEventDispatcher();

            foreach ($links as $link) {
                $stats = $this->processLink($link);

                if ($stats) {
                    $affectedPropertyIds[] = $link->property_id;
                    $totalStats['imported'] += $stats['imported'];
                    $totalStats['updated'] += $stats['updated'];
                    $totalStats['deleted'] += $stats['deleted'];
                }
            }

        } finally {
            // возвращаем Observers обратно
            SyncedBooking::setEventDispatcher(app('events'));

            // Снимаем флаг импорта
            $this->cacheService->markIcalImportFinished();

            // Сбрасываем кэш для всех затронутых объектов
            // Это критически важно — без этого пользователи будут видеть устаревшие данные
            if (!empty($affectedPropertyIds)) {
                $this->cacheService->invalidateAfterIcalImport(array_unique($affectedPropertyIds));
                $this->info("Кэш занятых дат сброшен для " . count(array_unique($affectedPropertyIds)) . " объектов");
            }
        }

        // Выводим итоговую статистику
        $this->newLine();
        $this->info('Синхронизация завершена!');
        $this->table(
            ['Показатель', 'Количество'],
            [
                ['Новых броней', $totalStats['imported']],
                ['Обновлено', $totalStats['updated']],
                ['Удалено', $totalStats['deleted']],
            ]
        );

        // Логируем для мониторинга
        Log::info('Синхронизация iCal завершена', [
            'stats' => $totalStats,
            'affected_properties' => array_unique($affectedPropertyIds),
        ]);

        return Command::SUCCESS;
    }

    private function processLink(CalendarLink $link): ?array
    {
        $this->line("Обрабатываем ссылку для объекта #{$link->property_id} ({$link->platform})");

        try {
            // 1. Проверяем ETag (оптимизация)
            $response = Http::timeout(30)->head($link->url);

            if (!$response->successful()) {
                $this->error("  ✗ Не удалось получить файл (HTTP {$response->status()})");
                Log::warning("iCal: HTTP error for link #{$link->id}", [
                    'status' => $response->status(),
                    'url' => $link->url,
                ]);
                return null;
            }

            $etag = $response->header('ETag');

            if ($etag && $etag === $link->etag) {
                $this->line("  → Файл не изменился (ETag совпадает), пропускаем");
                return null;
            }

            // 2. Скачиваем и парсим .ics файл
            $ical = new ICal($link->url, [
                'defaultSpan' => 2,
                'defaultTimeZone' => 'Europe/Moscow',
            ]);

            $events = $ical->events();
            $this->line("  → Найдено " . count($events) . " событий");

            // 3. Получаем все существующие синхронизированные брони
            $existingBookings = SyncedBooking::where('calendar_link_id', $link->id)
                ->get()
                ->keyBy('external_uid');

            $processedUids = [];
            $stats = ['imported' => 0, 'updated' => 0, 'deleted' => 0];

            // 4. Обрабатываем каждое событие
            foreach ($events as $event) {
                $externalUid = $event->uid ?? null;

                if (!$externalUid) {
                    $this->warn("  → Пропущено событие без UID");
                    continue;
                }

                $checkIn = $this->parseIcalDate($event->dtstart);
                $checkOut = $this->parseIcalDate($event->dtend);

                if (!$checkIn || !$checkOut) {
                    $this->warn("  → Пропущено событие с некорректными датами (UID: {$externalUid})");
                    continue;
                }

                $processedUids[] = $externalUid;
                $summary = $event->summary ?? "Бронь {$link->platform}";

                // Проверяем, есть ли уже такая бронь
                if ($existingBookings->has($externalUid)) {
                    $existing = $existingBookings->get($externalUid);

                    // Проверяем, изменились ли данные
                    if ($existing->check_in->format('Y-m-d') !== $checkIn->format('Y-m-d') ||
                        $existing->check_out->format('Y-m-d') !== $checkOut->format('Y-m-d') ||
                        $existing->summary !== $summary) {

                        // Обновляем через query builder (чтобы Observers точно не сработали)
                        DB::table('synced_bookings')
                            ->where('id', $existing->id)
                            ->update([
                                'check_in' => $checkIn,
                                'check_out' => $checkOut,
                                'summary' => $summary,
                                'updated_at' => now(),
                            ]);
                        $stats['updated']++;
                    }
                } else {
                    // Создаём через query builder
                    DB::table('synced_bookings')->insert([
                        'property_id' => $link->property_id,
                        'calendar_link_id' => $link->id,
                        'external_uid' => $externalUid,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'summary' => $summary,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['imported']++;
                }
            }

            // 5. Удаляем брони, которых больше нет в iCal
            $toDelete = $existingBookings->keys()->diff($processedUids);
            if ($toDelete->isNotEmpty()) {
                DB::table('synced_bookings')
                    ->where('calendar_link_id', $link->id)
                    ->whereIn('external_uid', $toDelete->toArray())
                    ->delete();
                $stats['deleted'] = $toDelete->count();
            }

            // 6. Обновляем ETag и время последней синхронизации
            $link->update([
                'etag' => $etag,
                'last_synced_at' => now(),
            ]);

            $this->info("  ✓ Завершено: +{$stats['imported']} ~{$stats['updated']} -{$stats['deleted']}");

            return $stats;

        } catch (\Exception $e) {
            Log::error("Ошибка синхронизации календаря #{$link->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error("  ✗ Ошибка: " . $e->getMessage());
            return null;
        }
    }

    private function parseIcalDate($dateValue): ?Carbon
    {
        if (!$dateValue) {
            return null;
        }

        if ($dateValue instanceof \DateTimeInterface) {
            return Carbon::instance($dateValue);
        }

        $dateString = trim((string) $dateValue);

        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $dateString, $matches)) {
            return Carbon::create($matches[1], $matches[2], $matches[3])->startOfDay();
        }

        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})(Z)?$/', $dateString, $matches)) {
            $carbon = Carbon::create(
                $matches[1], $matches[2], $matches[3],
                $matches[4], $matches[5], $matches[6]
            );

            if (isset($matches[7]) && $matches[7] === 'Z') {
                $carbon->setTimezone('Europe/Moscow');
            }

            return $carbon;
        }

        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning("Не удалось распарсить дату iCal: {$dateString}");
            return null;
        }
    }
}
