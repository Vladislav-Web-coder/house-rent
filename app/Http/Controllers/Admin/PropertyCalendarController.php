<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\SyncedBooking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;

class PropertyCalendarController extends Controller
{
    public function disabledDates(Property $property): JsonResponse
    {
        $today = Carbon::now();
        $yearAhead = $today->copy()->addYear();

        $disabledDates = [];

        // Подтверждённые брони
        $bookings = Booking::where('property_id', $property->id)
            ->where('status', 'confirmed')
            ->where('check_in', '<=', $yearAhead)
            ->where('check_out', '>', $today->toDateString())
            ->get();

        // Ожидающие и одобренные заявки
        $requests = BookingRequest::where('property_id', $property->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('check_in', '<=', $yearAhead)
            ->where('check_out', '>', $today->toDateString())
            ->get();

        // Синхронизированные брони
        $synced = SyncedBooking::where('property_id', $property->id)
            ->where('check_in', '<=', $yearAhead)
            ->where('check_out', '>', $today->toDateString())
            ->get();

        foreach ($bookings->merge($synced) as $b) {
            $start = $b->check_in->isBefore($today) ? $today->copy()->startOfDay() : $b->check_in;
            $period = CarbonPeriod::create($start, $b->check_out->copy()->subDay());
            foreach ($period as $date) {
                $disabledDates[] = $date->toDateString();
            }
        }

        foreach ($requests as $r) {
            $start = $r->check_in->isBefore($today) ? $today->copy()->startOfDay() : $r->check_in;
            $period = CarbonPeriod::create($start, $r->check_out->copy()->subDay());
            foreach ($period as $date) {
                $disabledDates[] = $date->toDateString();
            }
        }

        return response()->json(array_unique($disabledDates));
    }
}
