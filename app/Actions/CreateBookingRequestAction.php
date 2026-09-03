<?php

namespace App\Actions;

use App\Models\Property;
use App\Models\BookingRequest;
use App\Models\User;
use App\Notifications\NewBookingAdminNotification;
use App\Notifications\NewBookingRequestNotification;
use App\Services\BookingNotificationService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class CreateBookingRequestAction
{
    public function __construct(
        protected PricingService $pricingService,
        protected CheckAvailabilityAction $availabilityAction,
        protected BookingNotificationService $notificationService,
    ) {}

    public function execute(Property $property, array $data): BookingRequest
    {
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        // Проверка доступности (защита от race condition)
        $availability = $this->availabilityAction->execute($property, $checkIn, $checkOut);
        if (!$availability['available']) {
            throw new \DomainException($availability['message']);
        }

        // Рассчитываем итоговую цену
        $pricingData = $this->pricingService->calculateTotal($property, $checkIn, $checkOut);
        $totalPrice = $pricingData['final_total'];

        // Создаем заявку в БД
        $bookingRequest = BookingRequest::create([
            'property_id' => $property->id,
            'guest_name' => $data['guest_name'],
            'guest_phone' => $data['guest_phone'],
            'guest_email' => $data['guest_email'],
            'country_code' => $data['country_code'] ?? '+7',
            'contact_method' => $data['contact_method'] ?? 'telegram',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => $data['adults'] ?? 1,
            'children' => $data['children'] ?? 0,
            'total_price' => $totalPrice,
            'comment' => $data['comment'] ?? null,
            'status' => 'pending',
        ]);

        $this->notificationService->notifyClientAboutNewRequest($bookingRequest);

        $admin = User::where('role', 'admin')->whereNotNull('max_user_id')->get();
        $admin->each(function (User $admin) use ($property, $bookingRequest) {
            $admin->notify(new NewBookingAdminNotification($property, $bookingRequest));
        });
        return $bookingRequest;
    }
}
