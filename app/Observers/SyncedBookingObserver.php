<?php

namespace App\Observers;

use App\Models\SyncedBooking;
use App\Services\CacheService;

class SyncedBookingObserver
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function saved(SyncedBooking $syncedBooking): void
    {
        $this->cacheService->invalidateDisabledDates($syncedBooking->property_id);
        $this->cacheService->invalidatePropertyDetail($syncedBooking->property_id);
    }

    public function deleted(SyncedBooking $syncedBooking): void
    {
        $this->cacheService->invalidateDisabledDates($syncedBooking->property_id);
    }
}
