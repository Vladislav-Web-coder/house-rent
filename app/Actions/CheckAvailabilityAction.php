<?php

namespace App\Actions;

use App\Models\Property;
use Carbon\Carbon;

class CheckAvailabilityAction
{
    public function execute(Property $property, Carbon $checkIn, Carbon $checkOut): array
    {
        $checkInStr = $checkIn->toDateString();
        $checkOutStr = $checkOut->toDateString();

        // 1. Проверка: дата выезда должна быть строго позже заезда
        if ($checkOut->lte($checkIn)) {
            return ['available' => false, 'message' => 'Дата выезда должна быть позже даты заезда'];
        }

        // 2. Проверка: минимальное количество суток
        $nights = $checkIn->diffInDays($checkOut);
        if ($nights < $property->min_stay) {
            return ['available' => false, 'message' => "Минимальный срок проживания: {$property->min_stay} суток"];
        }

        // 3. Проверка подтвержденных броней (блокировка)
        $hasBookings = $property->bookings()
            ->where('status', 'confirmed')
            ->where(function ($q) use ($checkInStr, $checkOutStr) {
                // Стандартная логика пересечения: (StartA < EndB) и (EndA > StartB)
                $q->where('check_in', '<', $checkOutStr)
                    ->where('check_out', '>', $checkInStr);
            })->exists();

        if ($hasBookings) {
            return ['available' => false, 'message' => 'Эти даты уже забронированы'];
        }

        // 4. Проверка заявок в статусах pending/approved (временная блокировка от спама)
        $hasRequests = $property->bookingRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($checkInStr, $checkOutStr) {
                $q->where('check_in', '<', $checkOutStr)
                    ->where('check_out', '>', $checkInStr);
            })->exists();

        if ($hasRequests) {
            return ['available' => false, 'message' => 'На эти даты уже есть активная заявка на рассмотрении'];
        }

        // 5. Проверка внешних синхронизированных броней (Авито, Суточно и т.д.)
        $hasSynced = $property->syncedBookings()
            ->where(function ($q) use ($checkInStr, $checkOutStr) {
                $q->where('check_in', '<', $checkOutStr)
                    ->where('check_out', '>', $checkInStr);
            })->exists();

        if ($hasSynced) {
            return ['available' => false, 'message' => 'Эти даты заняты (внешнее бронирование)'];
        }

        return ['available' => true, 'message' => 'Даты свободны'];
    }
}
