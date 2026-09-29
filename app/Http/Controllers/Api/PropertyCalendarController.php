<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\PricingService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class PropertyCalendarController extends Controller
{
    public function __construct(
        protected PricingService $pricingService
    ) {}

    public function show(Property $property, Request $request)
    {
        // Ограничиваем период: иначе запрос с start/end на много лет создаст
        // огромный CarbonPeriod и «положит» сервер (DoS). Максимум — год вперёд.
        $floor = now()->startOfDay();
        $ceil = now()->addYear();

        $startDate = $request->get('start')
            ? Carbon::parse($request->get('start'))->max($floor)
            : now()->startOfMonth();
        $endDate = $request->get('end')
            ? Carbon::parse($request->get('end'))->min($ceil)
            : $ceil;

        if ($endDate->lt($startDate)) {
            return response()->json(['message' => 'Некорректный период'], 422);
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $calendarData = [];

        foreach ($period as $date) {
            // Получаем цену и источник
            $priceInfo = $this->pricingService->getDailyPriceWithSource($property, $date);

            // Проверяем доступность
            $isAvailable = true;
            $bookingInfo = null;

            // Проверка подтвержденных броней
            $booking = $property->bookings()
                ->where('status', 'confirmed')
                ->where('check_in', '<=', $date->toDateString())
                ->where('check_out', '>', $date->toDateString())
                ->first();

            if ($booking) {
                $isAvailable = false;
                $bookingInfo = [
                    'type' => 'booking',
                    'guest_name' => $booking->guest_name,
                ];
            }

            // Проверка заявок
            if ($isAvailable) {
                $request = $property->bookingRequests()
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('check_in', '<=', $date->toDateString())
                    ->where('check_out', '>', $date->toDateString())
                    ->first();

                if ($request) {
                    $isAvailable = false;
                    $bookingInfo = [
                        'type' => 'request',
                        'status' => $request->status,
                        'guest_name' => $request->guest_name,
                    ];
                }
            }

            // Проверка синхронизированных броней
            if ($isAvailable) {
                $synced = $property->syncedBookings()
                    ->where('check_in', '<=', $date->toDateString())
                    ->where('check_out', '>', $date->toDateString())
                    ->first();

                if ($synced) {
                    $isAvailable = false;
                    $bookingInfo = [
                        'type' => 'synced',
                        'source' => $synced->source,
                    ];
                }
            }

            $calendarData[] = [
                'date' => $date->format('d-m-Y'),
                'day_name' => $this->getDayNameRu($date),
                'price' => $priceInfo['price'],
                'source' => $priceInfo['source'],
                'priority' => $priceInfo['priority'],
                'is_available' => $isAvailable,
                'booking_info' => $bookingInfo,
            ];
        }

        return response()->json([
            'calendar' => $calendarData,
            'property' => [
                'id' => $property->id,
                'title' => $property->title,
                'base_price' => (float) $property->base_price,
            ],
        ]);
    }

    private function getDayNameRu(Carbon $date): string
    {
        $days = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return $days[$date->dayOfWeek];
    }
}
