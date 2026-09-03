<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\CacheService;

class BookingObserver
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function saved(Booking $booking): void
    {
        $this->cacheService->invalidateDisabledDates($booking->property_id);
        $this->cacheService->invalidatePropertyDetail($booking->property_id);
    }

    public function deleted(Booking $booking): void
    {
        $this->cacheService->invalidateDisabledDates($booking->property_id);
        $this->cacheService->invalidatePropertyDetail($booking->property_id);
    }
}
