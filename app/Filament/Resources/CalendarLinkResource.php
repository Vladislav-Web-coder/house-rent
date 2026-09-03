<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CalendarLinkResource\Pages;
use App\Models\CalendarLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CalendarLinkResource extends Resource
{
    protected static ?string $model = CalendarLink::class;
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'Ссылки на календари';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $pluralLabel = 'Внешние календари';
    protected static ?string $modelLabel = 'ссылка на календарь';
    protected static ?string $pluralModelLabel = 'ссылки на календарь';
    protected static ?string $title = 'Ссылки на календарь';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('property_id')
                    ->label('Объект')
                    ->relationship('property', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('platform')
                    ->label('Площадка')
                    ->options([
                        'avito' => 'Авито',
                        'sutochno' => 'Суточно.ру',
                        'ostrovok' => 'Островок',
                        'cian' => 'Циан',
                        'yandex' => 'Яндекс.Путешествия',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('url')
                    ->label('Ссылка на .ics файл')
                    ->url()
                    ->required()
                    ->maxLength(2000)
                    ->columnSpanFull()
                    ->helperText('Скопируйте ссылку на экспорт календаря из кабинета площадки'),

                Forms\Components\TextInput::make('etag')
                    ->label('ETag (автоматически)')
                    ->disabled()
                    ->visible(fn ($record) => $record && $record->etag),

                Forms\Components\DateTimePicker::make('last_synced_at')
                    ->label('Последняя синхронизация')
                    ->disabled()
                    ->visible(fn ($record) => $record && $record->last_synced_at),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.title')
                    ->label('Объект')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('platform')
                    ->label('Площадка')
                    ->colors([
                        'primary' => 'avito',
                        'success' => 'sutochno',
                        'warning' => 'ostrovok',
                        'info' => 'cian',
                        'secondary' => 'yandex',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'avito' => 'Авито',
                        'sutochno' => 'Суточно.ру',
                        'ostrovok' => 'Островок',
                        'cian' => 'Циан',
                        'yandex' => 'Яндекс',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('last_synced_at')
                    ->label('Последняя синхронизация')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('syncedBookings_count')
                    ->label('Броней')
                    ->counts('syncedBookings'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'avito' => 'Авито',
                        'sutochno' => 'Суточно.ру',
                        'ostrovok' => 'Островок',
                        'cian' => 'Циан',
                        'yandex' => 'Яндекс',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->modalHeading('Удалить запись?')
                    ->modalDescription('Это действие нельзя отменить. Продолжить?')
                    ->modalSubmitActionLabel('Да, удалить')
                    ->modalCancelActionLabel('Отмена'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Удалить выбранные')
                    ->modalHeading('Удалить выбранные записи?')
                    ->modalSubmitActionLabel('Да, удалить'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalendarLinks::route('/'),
            'create' => Pages\CreateCalendarLink::route('/create'),
            'edit' => Pages\EditCalendarLink::route('/{record}/edit'),
        ];
    }
}
