<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Сервис кэширования публичного API.
 *
 * Инвалидация реализована через Cache::tags() — драйвер кэша должен его
 * поддерживать (redis / memcached / array). Драйвер database теги НЕ
 * поддерживает, поэтому на нём работа сервиса завершится явной ошибкой
 * вместо тихой деградации.
 */
class CacheService
{
    // TTL в секундах
    public const TTL_PROPERTIES = 300;        // 5 минут - список объектов
    public const TTL_PROPERTY_DETAIL = 300;   // 5 минут - детали объекта
    public const TTL_DISABLED_DATES = 300;    // 5 минут - занятые даты
    public const TTL_PRICING = 600;           // 10 минут - расчёт цен

    // Кэш-теги (группы инвалидации)
    public const TAG_PROPERTIES = 'properties';            // список объектов
    public const TAG_PROPERTY_DETAIL = 'property-detail';  // детали объекта
    public const TAG_DISABLED_DATES = 'disabled-dates';    // занятые даты
    public const TAG_PRICING = 'pricing';                  // расчёт цен

    /**
     * Теги для деталей конкретного объекта.
     */
    protected function detailTags(int $propertyId): array
    {
        return [self::TAG_PROPERTY_DETAIL, "property:{$propertyId}"];
    }

    /**
     * Теги для занятых дат конкретного объекта.
     */
    protected function disabledDatesTags(int $propertyId): array
    {
        return [self::TAG_DISABLED_DATES, "property:{$propertyId}", "dates:{$propertyId}"];
    }

    /**
     * Список всех объектов
     */
    public function getPropertiesList(\Closure $callback): array
    {
        return Cache::tags([self::TAG_PROPERTIES])
            ->remember('properties:list', self::TTL_PROPERTIES, $callback);
    }

    /**
     * Детали конкретного объекта
     */
    public function getPropertyDetail(int $propertyId, \Closure $callback): mixed
    {
        return Cache::tags($this->detailTags($propertyId))
            ->remember("property:{$propertyId}:detail", self::TTL_PROPERTY_DETAIL, $callback);
    }

    /**
     * Занятые даты для объекта
     */
    public function getDisabledDates(int $propertyId, \Closure $callback): array
    {
        return Cache::tags($this->disabledDatesTags($propertyId))
            ->remember("property:{$propertyId}:disabled-dates", self::TTL_DISABLED_DATES, $callback);
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
        return Cache::tags([self::TAG_PRICING, "property:{$propertyId}"])
            ->remember("pricing:{$propertyId}:{$checkIn}:{$checkOut}", self::TTL_PRICING, $callback);
    }

    // ========== ИНВАЛИДАЦИЯ ==========

    /**
     * Сброс кэша списка объектов и деталей (при изменении объекта)
     */
    public function invalidateProperties(): void
    {
        Cache::tags([self::TAG_PROPERTIES, self::TAG_PROPERTY_DETAIL])->flush();
    }

    /**
     * Сброс кэша деталей конкретного объекта
     */
    public function invalidatePropertyDetail(int $propertyId): void
    {
        Cache::tags(["property:{$propertyId}", self::TAG_PROPERTY_DETAIL, self::TAG_PROPERTIES])->flush();
    }

    /**
     * Сброс кэша занятых дат (при изменении брони/заявки)
     */
    public function invalidateDisabledDates(?int $propertyId = null): void
    {
        if ($propertyId !== null) {
            // Сбрасываем только даты и цены этого объекта + общий список дат
            Cache::tags(["dates:{$propertyId}", "property:{$propertyId}", self::TAG_DISABLED_DATES])->flush();
        } else {
            Cache::tags([self::TAG_DISABLED_DATES])->flush();
        }
    }

    /**
     * Сброс кэша расчёта цен (при изменении цен)
     */
    public function invalidatePricing(?int $propertyId = null): void
    {
        if ($propertyId !== null) {
            Cache::tags([self::TAG_PRICING, "property:{$propertyId}"])->flush();
        } else {
            Cache::tags([self::TAG_PRICING])->flush();
        }
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
        return (bool) Cache::get('ical:importing', false);
    }

    /**
     * Сброс кэша после импорта внешних броней
     */
    public function invalidateAfterIcalImport(?array $propertyIds = null): void
    {
        if ($propertyIds) {
            foreach ($propertyIds as $pid) {
                Cache::tags(["dates:{$pid}", "property:{$pid}", self::TAG_DISABLED_DATES])->flush();
            }
        } else {
            Cache::tags([self::TAG_DISABLED_DATES])->flush();
        }

        // Логируем
        \Log::info('Кэш сброшен после импорта iCal', [
            'property_ids' => $propertyIds,
            'count' => $propertyIds ? count($propertyIds) : 'all',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
