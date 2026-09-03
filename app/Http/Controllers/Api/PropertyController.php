<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Services\AvailabilityService;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected CacheService $cacheService
    ) {}

    public function index(Request $request)
    {
        // Кэшируем ID объектов
        $propertyIds = $this->cacheService->getPropertiesList(function () {
            return Property::where('is_visible', true)
                ->orderBy('created_at', 'desc')
                ->pluck('id')
                ->toArray();
        });

        $properties = Property::whereIn('id', $propertyIds)
            ->with(['media'])
            ->get();

        $properties = $properties->sortBy(function ($property) use ($propertyIds) {
            return array_search($property->id, $propertyIds);
        })->values();

        return PropertyResource::collection($properties);
    }

    public function disabledDates(): JsonResponse
    {
        // Если идёт импорт — не используем кэш
        if ($this->cacheService->isIcalImporting()) {
            $result = $this->calculateFreshDisabledDates();
            return response()->json(['data' => $result]);
        }

        // Кэшируем результат
        $result = $this->cacheService->getAllDisabledDates(function () {
            return $this->calculateFreshDisabledDates();
        });

        return response()->json(['data' => $result]);
    }

    private function calculateFreshDisabledDates(): array
    {
        $properties = Property::where('is_visible', true)->get(['id']);
        $data = [];

        foreach ($properties as $property) {
            $data[$property->id] = $this->availabilityService->getDisabledDates($property);
        }

        return $data;
    }
}
