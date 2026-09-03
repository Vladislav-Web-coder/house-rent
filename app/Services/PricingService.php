<?php

namespace App\Services;

use App\Models\Property;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PricingService
{
    private array $cache = [];

    public function calculateTotal(Property $property, Carbon $checkIn, Carbon $checkOut): array
    {
        $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());
        $breakdown = [];
        $totalNights = 0;
        $accommodationTotal = 0;
        $priceGroups = [];

        foreach ($period as $date) {
            $priceInfo = $this->getDailyPriceWithSource($property, $date);
            $dailyPrice = $priceInfo['price'];
            $source = $priceInfo['source'];

            $breakdown[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $this->getDayNameRu($date),
                'price' => $dailyPrice,
                'source' => $source,
            ];

            $groupKey = ($source ?? 'default') . '_' . $dailyPrice;
            if (!isset($priceGroups[$groupKey])) {
                $priceGroups[$groupKey] = [
                    'source' => $source,
                    'price_per_night' => $dailyPrice,
                    'nights' => 0,
                    'subtotal' => 0,
                ];
            }
            $priceGroups[$groupKey]['nights']++;
            $priceGroups[$groupKey]['subtotal'] += $dailyPrice;

            $accommodationTotal += $dailyPrice;
            $totalNights++;
        }

        // ✅ Убрали cleaning_fee — итоговая цена = только проживание
        return [
            'nights' => $totalNights,
            'accommodation_total' => $accommodationTotal,
            'final_total' => $accommodationTotal, // Теперь равно accommodation_total
            'breakdown' => $breakdown,
            'price_groups' => array_values($priceGroups),
        ];
    }

    /**
     * Возвращает цену за сутки И источник этой цены (только если он осмысленный)
     */
    public function getDailyPriceWithSource(Property $property, Carbon $date): array
    {
        $cacheKey = $property->id . '_' . $date->toDateString();
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        // Приоритет 1: Точечное исключение (конкретная дата) - ВЫСШИЙ ПРИОРИТЕТ
        $exception = $property->priceExceptions()
            ->where('date', $date->toDateString())
            ->first();

        if ($exception) {
            $result = [
                'price' => (float) $exception->price,
                'source' => $exception->reason ?: 'Особая дата',
                'priority' => 1,
            ];
            $this->cache[$cacheKey] = $result;
            return $result;
        }

        // Приоритет 2: Сезонный период
        $period = $property->pricePeriods()
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->first();

        if ($period) {
            $result = [
                'price' => (float) $period->price,
                'source' => $period->name ?: 'Сезонный период',
                'priority' => 2,
            ];
            $this->cache[$cacheKey] = $result;
            return $result;
        }

        // Приоритет 3: Цена по дню недели
        $dayPrice = $property->priceByDayOfWeeks()
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_active', true)
            ->first();

        if ($dayPrice) {
            $result = [
                'price' => (float) $dayPrice->price,
                'source' => null, // Не показываем, просто цена
                'priority' => 3,
            ];
            $this->cache[$cacheKey] = $result;
            return $result;
        }

        // Приоритет 4: Базовая цена
        $result = [
            'price' => (float) ($property->base_price ?? 0),
            'source' => null,
            'priority' => 5,
        ];
        $this->cache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Русские названия дней недели
     */
    private function getDayNameRu(Carbon $date): string
    {
        $days = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return $days[$date->dayOfWeek];
    }
}
