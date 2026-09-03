<?php

namespace App\Filament\Resources\CalendarLinkResource\Pages;

use App\Filament\Resources\CalendarLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCalendarLinks extends ListRecords
{
    protected static string $resource = CalendarLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
