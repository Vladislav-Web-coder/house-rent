<?php

namespace App\Http\Controllers\Api;

use App\Models\Property;
use Carbon\Carbon;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class IcalController
{
    public function export(Property $property, string $token)
    {
        // 1. Проверка секретного токена (защита от посторонних)
        if (!$property->ical_export_token || $token !== $property->ical_export_token) {
            abort(403, 'Неверный токен календаря');
        }

        // 2. Создаем календарь
        $calendar = Calendar::create($property->title)
            ->description('Календарь занятости объекта: ' . $property->address)
            ->productIdentifier('-//BookingApp//RU');

        // 3. Ограничиваем выгрузку 2 годами вперед
        $maxDate = Carbon::now()->addYears(2);

        // 4. Собираем все события в массив
        $events = [];

        // Подтвержденные внутренние брони
        $bookings = $property->bookings()
            ->where('status', 'confirmed')
            ->where('check_in', '<=', $maxDate)
            ->get();

        foreach ($bookings as $booking) {
            $events[] = Event::create('Занято')
                ->startsAt($booking->check_in)
                ->endsAt($booking->check_out)
                ->fullDay()
                ->uniqueIdentifier('booking-' . $booking->id . '@yourdomain.com');
        }

        // Утвержденные заявки
        $requests = $property->bookingRequests()
            ->whereIn('status', ['approved', 'confirmed'])
            ->where('check_in', '<=', $maxDate)
            ->get();

        foreach ($requests as $request) {
            $events[] = Event::create('Резерв: ' . $request->guest_name)
                ->startsAt($request->check_in)
                ->endsAt($request->check_out)
                ->fullDay()
                ->uniqueIdentifier('request-' . $request->id . '@yourdomain.com');
        }

        // 5. Добавляем все события в календарь
        foreach ($events as $event) {
            $calendar->event($event);
        }

        // 6. Возвращаем правильный HTTP-ответ для .ics файла
        return response($calendar->get(), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="property-' . $property->id . '-calendar.ics"',
        ]);
    }
}
