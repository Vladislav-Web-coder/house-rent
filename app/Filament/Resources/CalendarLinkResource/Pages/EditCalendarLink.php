<?php

namespace App\Filament\Resources\CalendarLinkResource\Pages;

use App\Filament\Resources\CalendarLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCalendarLink extends EditRecord
{
    protected static string $resource = CalendarLinkResource::class;
    protected static ?string $title = 'Редактировать ссылку на календарь';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->modalHeading('Удалить ссылку на календарь?')
                ->modalDescription('Это действие нельзя отменить. Продолжить?')
                ->modalSubmitActionLabel('Да, удалить')
                ->modalCancelActionLabel('Отмена'),
        ];
    }
}
