<?php

namespace App\Actions;

use App\Enums\BookingRequestStatus;
use App\Models\Booking;
use App\Models\BookingRequest;
use Illuminate\Support\Facades\DB;

class ApproveBookingRequestAction
{
    public function execute(BookingRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $request->update([
                'status' => BookingRequestStatus::APPROVED,
            ]);

            // 2. Создаем фактическую бронь (она пойдет в iCal)
            Booking::create([
                'property_id' => $request->property_id,
                'booking_request_id' => $request->id,
                'source' => 'website',
                'guest_name' => $request->guest_name,
                'guest_phone' => $request->guest_phone,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'status' => 'confirmed',
            ]);

            // 3. Здесь позже добавим вызов SyncCalendarAction для обновления .ics файла
            // (Пока даты уже заняты внутри нашей системы, так как CheckAvailabilityAction
            // проверяет и статус 'approved' у заявок, и confirmed у броней).
        });

        // 4. (Опционально) Отправить гостю SMS/Telegram: "Ваша бронь подтверждена!"
    }
}
