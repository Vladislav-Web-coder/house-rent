<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // TTL в секундах
    public const TTL_PROPERTIES = 300;        // 5 минут — список объектов
    public const TTL_PROPERTY_DETAIL = 300;   // 5 минут — детали объекта
    public const TTL_DISABLED_DATES = 300;    // 5 минут — занятые даты
    public const TTL_PRICING = 600;           // 10 минут — расчёт цен

    // Кэш-теги для групповой инвалидации
    public const TAG_PROPERTIES = 'properties';
    public const TAG_PROPERTY_DETAIL = 'property-detail';
    public const TAG_DISABLED_DATES = 'disabled-dates';
    public const TAG_PRICING = 'pricing';

    /**
     * Список всех объектов
     */
    public function getPropertiesList(\Closure $callback): array
    {
        return Cache::tags([self::TAG_PROPERTIES])
            ->remember('properties:ids', self::TTL_PROPERTIES, $callback);
    }

    /**
     * Детали конкретного объекта
     */
    public function getPropertyDetail(int $propertyId, \Closure $callback): mixed
    {
        return Cache::tags([self::TAG_PROPERTY_DETAIL])
            ->remember("property:detail:{$propertyId}", self::TTL_PROPERTY_DETAIL, $callback);
    }

    /**
     * Занятые даты для объекта
     */
    public function getDisabledDates(int $propertyId, \Closure $callback): array
    {
        return Cache::tags([self::TAG_DISABLED_DATES])
            ->remember("disabled-dates:{$propertyId}", self::TTL_DISABLED_DATES, $callback);
    }

    /**
     * Занятые даты для всех объектов
     */
    public function getAllDisabledDates(\Closure $callback): array
    {
        return Cache::tags([self::TAG_DISABLED_DATES])
            ->remember('disabled-dates:all', self::TTL_DISABLED_DATES, $callback);
    }

    /**
     * Расчёт стоимости
     */
    public function getPricing(int $propertyId, string $checkIn, string $checkOut, \Closure $callback): array
    {
        $key = "pricing:{$propertyId}:{$checkIn}:{$checkOut}";
        return Cache::tags([self::TAG_PRICING])
            ->remember($key, self::TTL_PRICING, $callback);
    }

    // ========== ИНВАЛИДАЦИЯ ==========

    /**
     * Сброс кэша списка объектов и деталей (при изменении объекта)
     */
    public function invalidateProperties(): void
    {
        Cache::tags([self::TAG_PROPERTIES])->flush();
        Cache::tags([self::TAG_PROPERTY_DETAIL])->flush();
    }

    /**
     * Сброс кэша деталей конкретного объекта
     */
    public function invalidatePropertyDetail(int $propertyId): void
    {
        Cache::tags([self::TAG_PROPERTY_DETAIL])->flush();
        Cache::tags([self::TAG_PROPERTIES])->flush(); // список тоже меняем
    }

    /**
     * Сброс кэша занятых дат (при изменении брони/заявки)
     */
    public function invalidateDisabledDates(?int $propertyId = null): void
    {
        // Т.к. с тегами проще сбрасывать всё, сбрасываем все занятые даты
        Cache::tags([self::TAG_DISABLED_DATES])->flush();
    }

    /**
     * Сброс кэша расчёта цен (при изменении цен)
     */
    public function invalidatePricing(): void
    {
        Cache::tags([self::TAG_PRICING])->flush();
    }

    /**
     * Полный сброс всех кэшей (экстренный случай)
     */
    public function invalidateAll(): void
    {
        Cache::tags([
            self::TAG_PROPERTIES,
            self::TAG_PROPERTY_DETAIL,
            self::TAG_DISABLED_DATES,
            self::TAG_PRICING,
        ])->flush();
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
        return Cache::get('ical:importing', false);
    }

    /**
     * Сброс кэша после импорта внешних броней
     */
    public function invalidateAfterIcalImport(?array $propertyIds = null): void
    {
        // Сбрасываем занятые даты для всех объектов
        Cache::tags([self::TAG_DISABLED_DATES])->flush();

        // Сбрасываем детали объектов
        Cache::tags([self::TAG_PROPERTY_DETAIL])->flush();

        // Логируем
        \Log::info('Кэш сброшен после импорта iCal', [
            'property_ids' => $propertyIds,
            'count' => $propertyIds ? count($propertyIds) : 'all',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
