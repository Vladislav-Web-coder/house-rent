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
use Illuminate\Http\Request;

class PropertyCalendarController extends Controller
{
    public function disabledDates(Request $request, Property $property): JsonResponse
    {
        // Ограничиваем период: без лимита запрос с start/end на много лет
        // создаёт огромные CarbonPeriod и может «положить» сервер (DoS).
        $today = Carbon::now();
        $yearAhead = $today->copy()->addYear();

        $start = $request->get('start')
            ? Carbon::parse($request->get('start'))->max($today->copy()->startOfDay())
            : $today;
        $end = $request->get('end')
            ? Carbon::parse($request->get('end'))->min($yearAhead)
            : $yearAhead;

        $disabledDates = [];

        // Подтверждённые брони
        $bookings = Booking::where('property_id', $property->id)
            ->where('status', 'confirmed')
            ->where('check_in', '<=', $end)
            ->where('check_out', '>', $start->toDateString())
            ->get();

        // Ожидающие и одобренные заявки
        $requests = BookingRequest::where('property_id', $property->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('check_in', '<=', $end)
            ->where('check_out', '>', $start->toDateString())
            ->get();

        // Синхронизированные брони
        $synced = SyncedBooking::where('property_id', $property->id)
            ->where('check_in', '<=', $end)
            ->where('check_out', '>', $start->toDateString())
            ->get();

        foreach ($bookings->merge($synced) as $b) {
            $bStart = $b->check_in->isBefore($start) ? $start->copy() : $b->check_in;
            $bEnd = $b->check_out->copy()->subDay()->min($end);
            if ($bEnd->lt($bStart)) {
                continue;
            }
            $period = CarbonPeriod::create($bStart, $bEnd);
            foreach ($period as $date) {
                $disabledDates[] = $date->toDateString();
            }
        }

        foreach ($requests as $r) {
            $rStart = $r->check_in->isBefore($start) ? $start->copy() : $r->check_in;
            $rEnd = $r->check_out->copy()->subDay()->min($end);
            if ($rEnd->lt($rStart)) {
                continue;
            }
            $period = CarbonPeriod::create($rStart, $rEnd);
            foreach ($period as $date) {
                $disabledDates[] = $date->toDateString();
            }
        }

        return response()->json(array_unique($disabledDates));
    }
}
