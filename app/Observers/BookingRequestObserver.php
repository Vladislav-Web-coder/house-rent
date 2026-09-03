<?php

namespace App\Observers;

use App\Models\BookingRequest;
use App\Services\BookingNotificationService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class BookingRequestObserver
{
    public function __construct(
        protected CacheService $cacheService,
        protected BookingNotificationService $notificationService
    ) {}

    public function created(BookingRequest $bookingRequest): void
    {
        // Кэш сбрасываем
        $this->cacheService->invalidateDisabledDates($bookingRequest->property_id);
        $this->cacheService->invalidatePropertyDetail($bookingRequest->property_id);
    }

    public function updated(BookingRequest $bookingRequest): void
    {
        // Инвалидация кэша
        $this->cacheService->invalidateDisabledDates($bookingRequest->property_id);
        $this->cacheService->invalidatePropertyDetail($bookingRequest->property_id);

        // Если изменился статус — отправляем уведомление клиенту
        if ($bookingRequest->wasChanged('status')) {
            $this->notificationService->notifyClientAboutStatusChange($bookingRequest);
        }
    }

    public function deleted(BookingRequest $bookingRequest): void
    {
        $this->cacheService->invalidateDisabledDates($bookingRequest->property_id);
    }
}
