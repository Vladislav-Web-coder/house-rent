<?php

namespace App\Filament\Resources\CalendarLinkResource\Pages;

use App\Filament\Resources\CalendarLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCalendarLink extends CreateRecord
{
    protected static string $resource = CalendarLinkResource::class;
    protected static ?string $title = 'Создать ссылку на календарь';
}
