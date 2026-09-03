<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PropertyDetailResource;
use App\Models\Property;
use App\Services\AvailabilityService;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;

class PropertyDetailController
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected CacheService $cacheService
    ) {}

    public function show(Property $property): JsonResponse
    {
        if (!$property->is_visible) {
            abort(404);
        }

        // Если идёт импорт — не используем кэш
        if ($this->cacheService->isIcalImporting()) {
            return response()->json($this->getFreshPropertyData($property));
        }

        // Кэшируем результат (массив данных, не модель)
        $cached = $this->cacheService->getPropertyDetail($property->id, function () use ($property) {
            return $this->getFreshPropertyData($property);
        });

        return response()->json($cached);
    }

    private function getFreshPropertyData(Property $property): array
    {
        // Загружаем только нужные отношения для этого запроса
        $property->load([
            'media', // Spatie Media Library
            'bookings' => fn($q) => $q
                ->where('status', 'confirmed')
                ->where('check_out', '>', now())
                ->select('id', 'property_id', 'check_in', 'check_out'),
            'bookingRequests' => fn($q) => $q
                ->whereIn('status', ['pending', 'approved'])
                ->where('check_out', '>', now())
                ->select('id', 'property_id', 'check_in', 'check_out'),
            'syncedBookings' => fn($q) => $q
                ->where('check_out', '>', now())
                ->select('id', 'property_id', 'check_in', 'check_out'),
        ]);

        return [
            'data' => (new PropertyDetailResource($property))->toArray(request()),
            'disabled_dates' => $this->availabilityService->getDisabledDates($property),
        ];
    }
}
