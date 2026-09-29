<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Сервис кэширования публичного API.
 *
 * ВАЖНО: драйвер кэша по умолчанию (database) НЕ поддерживает Cache::tags,
 * поэтому вместо теговой инвалидации используется механизм "поколений" (epoch):
 * при изменении данных инкрементируется счётчик-поколение, который входит
 * в ключ кэша. Старые записи просто перестают попадать под новые ключи
 * и вытесняются по TTL.
 */
class CacheService
{
    // TTL в секундах
    public const TTL_PROPERTIES = 300;        // 5 минут - список объектов
    public const TTL_PROPERTY_DETAIL = 300;   // 5 минут - детали объекта
    public const TTL_DISABLED_DATES = 300;    // 5 минут - занятые даты
    public const TTL_PRICING = 600;           // 10 минут - расчёт цен

    // Группировки инвалидации (аналоги прежних кэш-тегов)
    public const GROUP_PROPERTIES = 'properties';            // список объектов
    public const GROUP_PROPERTY_DETAIL = 'property-detail';  // детали объекта
    public const GROUP_DISABLED_DATES = 'disabled-dates';    // занятые даты
    public const GROUP_PRICING = 'pricing';                  // расчёт цен

    private const EPOCH_PREFIX = 'cache:epoch:';

    /**
     * Текущее поколение кэша для группы.
     */
    protected function epoch(string $group): int
    {
        return (int) Cache::get(self::EPOCH_PREFIX . $group, 0);
    }

    /**
     * Полное имя ключа с учётом поколения.
     */
    protected function key(string $group, string $key): string
    {
        return self::EPOCH_PREFIX . $group . ':' . $this->epoch($group) . ':' . $key;
    }

    /**
     * Сброс группы: увеличиваем поколение - все старые ключи становятся неактуальны.
     */
    protected function bumpEpoch(string $group): void
    {
        Cache::increment(self::EPOCH_PREFIX . $group);
    }

    /**
     * Список всех объектов
     */
    public function getPropertiesList(\Closure $callback): array
    {
        return Cache::remember(
            $this->key(self::GROUP_PROPERTIES, 'list:ids'),
            self::TTL_PROPERTIES,
            $callback
        );
    }

    /**
     * Детали конкретного объекта
     */
    public function getPropertyDetail(int $propertyId, \Closure $callback): mixed
    {
        return Cache::remember(
            $this->key(self::GROUP_PROPERTY_DETAIL, "detail:{$propertyId}"),
            self::TTL_PROPERTY_DETAIL,
            $callback
        );
    }

    /**
     * Занятые даты для объекта
     */
    public function getDisabledDates(int $propertyId, \Closure $callback): array
    {
        return Cache::remember(
            $this->key(self::GROUP_DISABLED_DATES, "disabled-dates:{$propertyId}"),
            self::TTL_DISABLED_DATES,
            $callback
        );
    }

    /**
     * Занятые даты для всех объектов
     */
    public function getAllDisabledDates(\Closure $callback): array
    {
        return Cache::remember(
            $this->key(self::GROUP_DISABLED_DATES, 'disabled-dates:all'),
            self::TTL_DISABLED_DATES,
            $callback
        );
    }

    /**
     * Расчёт стоимости
     */
    public function getPricing(int $propertyId, string $checkIn, string $checkOut, \Closure $callback): array
    {
        return Cache::remember(
            $this->key(self::GROUP_PRICING, "pricing:{$propertyId}:{$checkIn}:{$checkOut}"),
            self::TTL_PRICING,
            $callback
        );
    }

    // ========== ИНВАЛИДАЦИЯ ==========

    /**
     * Сброс кэша списка объектов и деталей (при изменении объекта)
     */
    public function invalidateProperties(): void
    {
        $this->bumpEpoch(self::GROUP_PROPERTIES);
        $this->bumpEpoch(self::GROUP_PROPERTY_DETAIL);
    }

    /**
     * Сброс кэша деталей конкретного объекта
     */
    public function invalidatePropertyDetail(int $propertyId): void
    {
        $this->bumpEpoch(self::GROUP_PROPERTY_DETAIL);
        $this->bumpEpoch(self::GROUP_PROPERTIES); // список тоже меняем
    }

    /**
     * Сброс кэша занятых дат (при изменении брони/заявки)
     */
    public function invalidateDisabledDates(?int $propertyId = null): void
    {
        $this->bumpEpoch(self::GROUP_DISABLED_DATES);
    }

    /**
     * Сброс кэша расчёта цен (при изменении цен)
     */
    public function invalidatePricing(): void
    {
        $this->bumpEpoch(self::GROUP_PRICING);
    }

    /**
     * Полный сброс всех кэшей (экстренный случай)
     */
    public function invalidateAll(): void
    {
        foreach ([
            self::GROUP_PROPERTIES,
            self::GROUP_PROPERTY_DETAIL,
            self::GROUP_DISABLED_DATES,
            self::GROUP_PRICING,
        ] as $group) {
            $this->bumpEpoch($group);
        }
    }

    /**
     * Пометить, что начался импорт iCal
     */
    public function markIcalImportStarted(): void
    {
        Cache::put('ical:importing', true, 600); // 10 минут максимум
    }

    /**
     * Снять флаг импорта iCal
     */
    public function markIcalImportFinished(): void
    {
        Cache::forget('ical:importing');
    }

    /**
     * Проверить, идёт ли сейчас импорт iCal
     */
    public function isIcalImporting(): bool
    {
        return (bool) Cache::get('ical:importing', false);
    }

    /**
     * Сброс кэша после импорта внешних броней
     */
    public function invalidateAfterIcalImport(?array $propertyIds = null): void
    {
        // Сбрасываем занятые даты для всех объектов
        $this->bumpEpoch(self::GROUP_DISABLED_DATES);

        // Сбрасываем детали объектов
        $this->bumpEpoch(self::GROUP_PROPERTY_DETAIL);

        // Логируем
        \Log::info('Кэш сброшен после импорта iCal', [
            'property_ids' => $propertyIds,
            'count' => $propertyIds ? count($propertyIds) : 'all',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
