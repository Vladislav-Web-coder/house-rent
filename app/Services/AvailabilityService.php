<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\SyncedBooking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function getDisabledDates(Property $property, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? Carbon::now()->startOfDay();
        $to = $to ?? Carbon::now()->addYear();

        $disabledDates = [];

        // 1. Подтверждённые брони
        $bookings = Booking::where('property_id', $property->id)
            ->where('status', 'confirmed')
            ->where('check_out', '>', $from)
            ->where('check_in', '<=', $to)
            ->get();

        // 2. Ожидающие и одобренные заявки
        $requests = BookingRequest::where('property_id', $property->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('check_out', '>', $from)
            ->where('check_in', '<=', $to)
            ->get();

        // 3. Синхронизированные брони (iCal)
        $synced = class_exists(SyncedBooking::class)
            ? SyncedBooking::where('property_id', $property->id)
                ->where('check_out', '>', $from)
                ->where('check_in', '<=', $to)
                ->get()
            : collect();

        $allBookings = collect()
            ->merge($bookings)
            ->merge($requests)
            ->merge($synced);

        foreach ($allBookings as $booking) {
            $start = Carbon::parse($booking->check_in);
            $end = Carbon::parse($booking->check_out)->subDay(); // Выезд не включаем

            if ($start->isBefore($from)) {
                $start = $from->copy();
            }
            if ($end->isAfter($to)) {
                $end = $to->copy();
            }

            if ($start->gt($end)) {
                continue;
            }

            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $disabledDates[] = $date->toDateString();
            }
        }

        return array_values(array_unique($disabledDates));
    }
}
