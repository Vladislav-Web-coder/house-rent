<?php

namespace App\Services;

use App\Mail\BookingRequestReceivedMail;
use App\Mail\BookingRequestStatusChangedMail;
use App\Models\BookingRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingNotificationService
{
    public function __construct(
        protected ContractFileProvider $contractFileProvider
    ) {}

    /**
     * Отправляет уведомление клиенту о получении заявки с готовым договором
     */
    public function notifyClientAboutNewRequest(BookingRequest $bookingRequest): bool
    {
        try {
            $bookingRequest->load('property');

            $contractPath = $this->contractFileProvider->getPath();

            if (!$contractPath) {
                Log::warning('Отправляем письмо без договора — файл не найден', [
                    'booking_request_id' => $bookingRequest->id,
                ]);
            }

            // Отправляем письмо через очередь
            Mail::to($bookingRequest->guest_email)
                ->queue(new BookingRequestReceivedMail(
                    $bookingRequest,
                    $contractPath
                ));

            Log::info('Уведомление клиенту поставлено в очередь', [
                'booking_request_id' => $bookingRequest->id,
                'contract_attached' => $contractPath !== null,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка отправки уведомления клиенту', [
                'booking_request_id' => $bookingRequest->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Отправляет уведомление об изменении статуса заявки
     */
    public function notifyClientAboutStatusChange(BookingRequest $bookingRequest): bool
    {
        if (!$bookingRequest->guest_email) {
            return false;
        }

        try {
            $bookingRequest->load('property');

            Mail::to($bookingRequest->guest_email)
                ->queue(new BookingRequestStatusChangedMail($bookingRequest));

            Log::info('Уведомление о смене статуса поставлено в очередь', [
                'booking_request_id' => $bookingRequest->id,
                'new_status' => $bookingRequest->status->value,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка отправки уведомления о статусе', [
                'booking_request_id' => $bookingRequest->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
