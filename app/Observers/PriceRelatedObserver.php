<?php

namespace App\Observers;

use App\Services\CacheService;

class PriceRelatedObserver
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function saved($model): void
    {
        $this->cacheService->invalidatePricing();

        if (property_exists($model, 'property_id') && $model->property_id) {
            $this->cacheService->invalidatePropertyDetail($model->property_id);
        }
    }

    public function deleted($model): void
    {
        $this->cacheService->invalidatePricing();

        if (property_exists($model, 'property_id') && $model->property_id) {
            $this->cacheService->invalidatePropertyDetail($model->property_id);
        }
    }
}
