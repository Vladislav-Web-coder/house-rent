<?php

namespace App\Filament\Resources\BookingRequestResource\Pages;

use App\Filament\Resources\BookingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookingRequest extends EditRecord
{
    protected static string $resource = BookingRequestResource::class;

    //  Перевод заголовка страницы
    protected static ?string $title = 'Редактировать заявку на бронирование';

    //  Перевод уведомления при сохранении
    protected function getSavedNotificationTitle(): string
    {
        return 'Заявка на бронирование сохранена';
    }

    //  Перевод уведомления при удалении
    protected function getDeletedNotificationTitle(): string
    {
        return 'Заявка на бронирование удалена';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->modalHeading('Удалить заявку?')
                ->modalDescription('Это действие нельзя отменить. Продолжить?')
                ->modalSubmitActionLabel('Да, удалить')
                ->modalCancelActionLabel('Отмена'),
        ];
    }
}
