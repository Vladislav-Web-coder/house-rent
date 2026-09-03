<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected static ?string $title = 'Редактировать объект';

    protected function getSavedNotificationTitle(): string
    {
        return 'Объект сохранён';
    }

    protected function getDeletedNotificationTitle(): string
    {
        return 'Объект удалён';
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
