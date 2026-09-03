<?php

namespace App\Observers;

use App\Models\Property;
use App\Services\CacheService;

class PropertyObserver
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function saved(Property $property): void
    {
        $this->cacheService->invalidatePropertyDetail($property->id);
    }

    public function deleted(Property $property): void
    {
        $this->cacheService->invalidateProperties();
    }
}
