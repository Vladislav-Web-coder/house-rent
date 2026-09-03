<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Filament\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $modelLabel = 'объект';
    protected static ?string $pluralModelLabel = 'объекты';
    protected static ?string $navigationLabel = 'Объекты';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $title = 'Объекты';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === ФОТО И ВИДЕО ГАЛЕРЕЯ ===
                Forms\Components\Section::make('Медиагалерея')
                    ->description('Фото и видео объекта. Первое медиа — главное. Видео: только MP4, до 100 МБ и 3 минут.')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('media')
                            ->collection('gallery')
                            ->label('Фото и видео')
                            ->multiple()
                            ->reorderable()
                            ->panelLayout('grid')
                            ->maxSize(102400) // 100 МБ
                            ->maxFiles(35)
                            ->acceptedFileTypes([
                                // Изображения
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                // Видео — только MP4 (максимальная совместимость)
                                'video/mp4',
                            ])
                            ->downloadable()
                            ->openable()
                            ->helperText('📷 Фото: JPG, PNG, WebP | 🎬 Видео: MP4 (H.264), до 100 МБ, до 3 минут')
                            ->columnSpanFull(),
                    ]),

                // === ОСНОВНАЯ ИНФОРМАЦИЯ ===
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название объекта')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Полное описание')
                            ->required()
                            ->columnSpanFull()
                            ->rows(6),

                        Forms\Components\Select::make('category')
                            ->label('Тип объекта')
                            ->options([
                                'house' => 'Дом',
                                'apartment' => 'Квартира',
                            ])
                            ->required()
                            ->default('house'),

                        Forms\Components\TextInput::make('area')
                            ->label('Площадь')
                            ->numeric()
                            ->suffix('м²')
                            ->minValue(1)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('rooms')
                            ->label('Количество комнат')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('base_price')
                            ->label('Базовая цена за сутки')
                            ->numeric()
                            ->required()
                            ->prefix('₽')
                            ->minValue(1)
                            ->helperText('Цена по умолчанию. Может быть переопределена сезонными периодами.')
                            ->columnSpan(1),
                    ])->columns(2),

                // === ПАРАМЕТРЫ ЖИЛЬЯ ===
                Forms\Components\Section::make('Вместимость и правила')
                    ->schema([
                        Forms\Components\TextInput::make('max_adults')
                            ->label('Макс. взрослых')
                            ->numeric()
                            ->default(2)
                            ->minValue(1)
                            ->maxValue(20)
                            ->required(),

                        Forms\Components\TextInput::make('max_children')
                            ->label('Макс. детей')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(20)
                            ->required(),

                        Forms\Components\TextInput::make('min_stay')
                            ->label('Минимальное кол-во суток')
                            ->numeric()
                            ->default(2)
                            ->minValue(1)
                            ->maxValue(30)
                            ->required()
                            ->helperText('Меньше этого количества ночей забронировать нельзя'),
                    ])->columns(3),

                // === ЛОКАЦИЯ ===
                Forms\Components\Section::make('Расположение')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Адрес')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Широта (Latitude)')
                                    ->numeric()
                                    ->step(0.0000001)
                                    ->placeholder('Например: 61.123456'),

                                Forms\Components\TextInput::make('longitude')
                                    ->label('Долгота (Longitude)')
                                    ->numeric()
                                    ->step(0.0000001)
                                    ->placeholder('Например: 30.123456'),
                            ])->columnSpanFull(),
                    ])->columns(1),

                // === НАСТРОЙКИ И iCal ===
                Forms\Components\Section::make('Публикация и синхронизация')
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Опубликован на сайте')
                            ->default(true)
                            ->helperText('Снимите галочку, чтобы скрыть объект с главной страницы.'),

                        Forms\Components\Section::make('Синхронизация календаря (iCal)')
                            ->description('Используется для двусторонней синхронизации с Авито, Суточно.ру и др.')
                            ->collapsible()
                            ->collapsed(fn ($record) => !$record || !$record->ical_export_token)
                            ->schema([
                                Forms\Components\TextInput::make('ical_export_token')
                                    ->label('Секретный токен')
                                    ->disabled()
                                    ->hintIcon('heroicon-m-information-circle', tooltip: 'Генерируется автоматически при создании объекта.')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('ical_export_url')
                                    ->label('Ссылка для импорта (ICS URL)')
                                    ->formatStateUsing(function ($record) {
                                        if (!$record || !$record->ical_export_token) {
                                            return 'Сначала сохраните объект, чтобы получить ссылку';
                                        }
                                        return route('api.ical.export', [
                                            'property' => $record->id,
                                            'token' => $record->ical_export_token
                                        ]);
                                    })
                                    ->disabled()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copy')
                                            ->icon('heroicon-m-clipboard-document')
                                            ->tooltip('Скопировать ссылку')
                                            ->action(function ($livewire, $state) {
                                                $livewire->dispatch('copy-to-clipboard', text: $state);
                                            })
                                    )
                                    ->hint('Скопируйте эту ссылку в настройки календаря на внешних площадках')
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('main_photo')
                    ->collection('gallery')
                    ->label('Главное')
                    ->limit(1)
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->weight('bold')
                    ->limit(30),

                Tables\Columns\TextColumn::make('category')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'house' => '🏠 Дом',
                        'apartment' => '🏢 Квартира',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'house' => 'success',
                        'apartment' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Цена/сутки')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rooms')
                    ->label('Комнат')
                    ->sortable(),

                Tables\Columns\TextColumn::make('area')
                    ->label('Площадь')
                    ->suffix(' м²')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_adults')
                    ->label('Гостей')
                    ->formatStateUsing(fn ($record) => $record->max_adults + $record->max_children)
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Опубликован'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Статус публикации')
                    ->trueLabel('Опубликованные')
                    ->falseLabel('Скрытые')
                    ->native(false),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Тип объекта')
                    ->options([
                        'house' => 'Дома',
                        'apartment' => 'Квартиры',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Создать объект'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),

                Tables\Actions\Action::make('calendar')
                    ->label('Календарь')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->url(fn (Property $record) => PropertyResource::getUrl('calendar', ['record' => $record])),

                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->modalHeading('Удалить объект?')
                    ->modalDescription('Это действие нельзя отменить. Все связанные заявки и бронирования также будут удалены. Продолжить?')
                    ->modalSubmitActionLabel('Да, удалить')
                    ->modalCancelActionLabel('Отмена'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->modalHeading('Удалить выбранные объекты?')
                        ->modalDescription('Это действие нельзя отменить. Все связанные данные будут удалены. Продолжить?')
                        ->modalSubmitActionLabel('Да, удалить'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Нет объектов')
            ->emptyStateDescription('Создайте первый объект, чтобы начать работу.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Создать объект'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PricePeriodsRelationManager::class,
            RelationManagers\PriceExceptionsRelationManager::class,
            RelationManagers\PriceByDayOfWeeksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
            'calendar' => Pages\PropertyCalendar::route('/{record}/calendar'),
        ];
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['ical_export_token'])) {
            $data['ical_export_token'] = Str::random(32);
        }
        return $data;
    }
}
