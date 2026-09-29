<?php

namespace App\Http\Controllers\Api;

use App\Actions\CheckAvailabilityAction;
use App\Models\Property;
use App\Services\CacheService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController
{
    public function __construct(
        protected CacheService $cacheService,
        protected PricingService $pricingService,
    ){}

    public function calculate(
        Property $property,
        Request $request,
        PricingService $pricingService,
        CheckAvailabilityAction $availabilityAction
    ): JsonResponse {
        // Скрытые объекты не должны быть доступны через публичный API
        if (!$property->is_visible) {
            return response()->json(['message' => 'Объект не найден'], 404);
        }

        $request->validate([
            'check_in' => 'required|date|date_format:Y-m-d',
            'check_out' => 'required|date|date_format:Y-m-d|after:check_in',
        ]);

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        // Проверяем доступность
        $availability = $availabilityAction->execute($property, $checkIn, $checkOut);

        if (!$availability['available']) {
            return response()->json([
                'available' => false,
                'message' => $availability['message'],
            ], 409);
        }

        // Рассчитываем цену
        $pricingData = $this->cacheService->getPricing(
            $property->id,
            $request->check_in,
            $request->check_out,
            function () use ($property, $checkIn, $checkOut) {
                return $this->pricingService->calculateTotal($property, $checkIn, $checkOut);
            }
        );

        return response()->json([
            'available' => true,
            'pricing' => $pricingData,
        ]);
    }
}
