<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Max\MaxChannel;
use NotificationChannels\Max\MaxMessage;

class NewBookingAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Property $property,
        public BookingRequest $booking
    ) {}

    public function via($notifiable): array
    {
        return [MaxChannel::class];
    }

    public function toMax($notifiable): MaxMessage
    {
        $text = "*Новая заявка на бронирование!*\n\n"
            . "*Объект:* {$this->property->title}\n"
            . "*Даты:* {$this->booking->check_in->format('d.m.Y')} – {$this->booking->check_out->format('d.m.Y')}\n"
            . "*Гость:* {$this->booking->guest_name}\n"
            . "*Способ связи:* {$this->booking->contact_method}\n"
            . "*Телефон:* {$this->booking->guest_phone}\n"
            . "*Сумма:* " . number_format($this->booking->total_price, 0, '.', ' ') . " ₽";

        if (!empty($this->booking->comment)) {
            $text .= "\n *Комментарий:* _{$this->booking->comment}_";
        }
        $text .= "\n Необходимо связаться с гостем и подтвердить бронь.";

        return MaxMessage::create($text)
            ->markdown();
//            ->inlineKeyboard([
//                [
//                    ['type' => 'link', 'text' => '📋 Открыть заявку', 'url' => url("/admin/booking-requests")],
//                ],
//            ]);
    }
}
