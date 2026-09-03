<?php

namespace App\Filament\Resources\BookingRequestResource\Pages;

use App\Filament\Resources\BookingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookingRequest extends CreateRecord
{
    protected static string $resource = BookingRequestResource::class;

    protected static ?string $title = 'Создать заявку на бронирование';

    protected function getCreatedNotificationTitle(): string
    {
        return 'Заявка на бронирование создана';
    }
}
