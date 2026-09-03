<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingRequestResource\Pages;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class BookingRequestResource extends Resource
{
    protected static ?string $model = BookingRequest::class;

    protected static ?string $modelLabel = 'заявка на бронирование';
    protected static ?string $pluralModelLabel = 'заявки на бронирование';
    protected static ?string $navigationLabel = 'Заявки на бронирование';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $title = 'Заявки на бронирование';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Выбор объекта
                Forms\Components\Select::make('property_id')
                    ->label('Объект')
                    ->relationship('property', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('check_in', null);
                        $set('check_out', null);
                        $set('total_price', null);
                    })
                    ->columnSpanFull(),

                // Дата заезда
                Forms\Components\DatePicker::make('check_in')
                    ->label('Дата заезда')
                    ->required()
                    ->minDate(now())
                    ->live()
                    ->disabledDates(function (Get $get) {
                        return static::getDisabledDates($get('property_id'));
                    })
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $checkOut = $get('check_out');
                        if ($checkOut && $checkOut <= $get('check_in')) {
                            $set('check_out', null);
                        }
                        static::calculatePrice($set, $get);
                    })
                    ->displayFormat('d.m.Y')
                    ->native(false),

                // Дата выезда
                Forms\Components\DatePicker::make('check_out')
                    ->label('Дата выезда')
                    ->required()
                    ->live()
                    ->minDate(function (Get $get) {
                        $checkIn = $get('check_in');
                        return $checkIn ? Carbon::parse($checkIn)->addDay() : now();
                    })
                    ->disabledDates(function (Get $get) {
                        return static::getDisabledDates($get('property_id'));
                    })
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        static::calculatePrice($set, $get);
                    })
                    ->displayFormat('d.m.Y')
                    ->native(false),

                // Информационный блок с выбранными датами
                Forms\Components\Placeholder::make('selected_dates_info')
                    ->label('')
                    ->content(function (Get $get): HtmlString {
                        $checkIn = $get('check_in');
                        $checkOut = $get('check_out');

                        if (!$checkIn && !$checkOut) {
                            return new HtmlString('<div class="text-gray-500 text-sm">Выберите даты заезда и выезда</div>');
                        }

                        $in = $checkIn ? Carbon::parse($checkIn)->translatedFormat('d M Y') : '—';
                        $out = $checkOut ? Carbon::parse($checkOut)->translatedFormat('d M Y') : '—';

                        $nightsHtml = '';
                        if ($checkIn && $checkOut) {
                            $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

                            if ($nights === 1) {
                                $nightsText = 'ночь';
                            } elseif ($nights >= 2 && $nights <= 4) {
                                $nightsText = 'ночи';
                            } else {
                                $nightsText = 'ночей';
                            }

                            $nightsHtml = "<div><strong class=\"text-[#283e46] dark:text-white\">{$nights} {$nightsText}</strong></div>";
                        }

                        return new HtmlString("
                            <div class=\"bg-[#ebf7fb] dark:bg-blue-900/20 rounded-lg p-4\">
                                <div class=\"flex items-center justify-between text-sm\">
                                    <div>
                                        <span class=\"text-gray-500 dark:text-gray-400\">Заезд:</span>
                                        <strong class=\"text-[#283e46] dark:text-white ml-2\">{$in}</strong>
                                    </div>
                                    <div>
                                        <span class=\"text-gray-500 dark:text-gray-400\">Выезд:</span>
                                        <strong class=\"text-[#283e46] dark:text-white ml-2\">{$out}</strong>
                                    </div>
                                    {$nightsHtml}
                                </div>
                            </div>
                        ");
                    })
                    ->live()
                    ->columnSpanFull(),

                // Итоговая стоимость
                Forms\Components\TextInput::make('total_price')
                    ->label('Итоговая стоимость')
                    ->numeric()
                    ->prefix('₽')
                    ->required()
                    ->helperText('Рассчитывается автоматически. Можно изменить вручную.')
                    ->live()
                    ->columnSpanFull(),

                // Детализация стоимости через PricingService
                Forms\Components\Placeholder::make('price_breakdown')
                    ->label('')
                    ->content(function (Get $get): HtmlString {
                        $propertyId = $get('property_id');
                        $checkIn = $get('check_in');
                        $checkOut = $get('check_out');

                        if (!$propertyId || !$checkIn || !$checkOut) {
                            return new HtmlString('');
                        }

                        $property = Property::find($propertyId);
                        if (!$property) {
                            return new HtmlString('');
                        }

                        try {
                            $checkInDate = Carbon::parse($checkIn);
                            $checkOutDate = Carbon::parse($checkOut);

                            $pricingService = app(PricingService::class);
                            $pricing = $pricingService->calculateTotal($property, $checkInDate, $checkOutDate);

                            $html = '<div class="bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-3 text-sm shadow-sm">';
                            $html .= '<div class="font-semibold text-gray-900 dark:text-gray-100 mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">Детализация стоимости:</div>';

                            foreach ($pricing['price_groups'] as $group) {
                                $nightsText = $group['nights'] === 1 ? 'ночь' : ($group['nights'] >= 2 && $group['nights'] <= 4 ? 'ночи' : 'ночей');
                                $sourceLabel = $group['source'] ? $group['source'] : 'Стандартная цена';

                                $html .= sprintf(
                                    '<div class="flex justify-between items-center py-2">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">%s: <span class="text-gray-900 dark:text-gray-100">%d %s</span></span>
                                        <span class="text-gray-900 dark:text-white font-semibold text-base">%s ₽</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 -mt-1 mb-1">%s ₽ за ночь</div>',
                                    e($sourceLabel),
                                    $group['nights'],
                                    $nightsText,
                                    number_format($group['subtotal'], 0, '.', ' '),
                                    number_format($group['price_per_night'], 0, '.', ' ')
                                );
                            }

                            $html .= sprintf(
                                '<div class="flex justify-between items-center border-t-2 border-gray-300 dark:border-gray-600 pt-3 mt-3">
                                    <span class="font-bold text-gray-900 dark:text-white text-base">Итого:</span>
                                    <span class="font-bold text-xl text-[#283e46] dark:text-[#77c4db]">%s ₽</span>
                                </div>',
                                number_format($pricing['final_total'], 0, '.', ' ')
                            );

                            $html .= '</div>';

                            return new HtmlString($html);
                        } catch (\Exception $e) {
                            return new HtmlString('<div class="text-red-500 dark:text-red-400 text-sm">Ошибка расчёта цены</div>');
                        }
                    })
                    ->live()
                    ->columnSpanFull(),

                // Секция "Данные гостя"
                Forms\Components\Section::make('Данные гостя')
                    ->schema([
                        Forms\Components\TextInput::make('guest_name')
                            ->label('Имя гостя')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('guest_phone')
                            ->label('Телефон')
                            ->required()
                            ->tel()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('country_code')
                            ->label('Код страны')
                            ->maxLength(10)
                            ->placeholder('+7'),

                        Forms\Components\TextInput::make('guest_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('На этот email отправляются договор и уведомления'),

                        Forms\Components\Select::make('contact_method')
                            ->label('Способ связи')
                            ->options([
                                'telegram' => 'Telegram',
                                'whatsapp' => 'WhatsApp',
                                'max' => 'MAX',
                                'phone' => 'Звонок',
                            ])
                            ->default('telegram'),
                    ])->columns(2),

                // Секция "Гости"
                Forms\Components\Section::make('Количество гостей')
                    ->schema([
                        Forms\Components\TextInput::make('adults')
                            ->label('Взрослые')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(2),

                        Forms\Components\TextInput::make('children')
                            ->label('Дети')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])->columns(2),

                // Комментарий
                Forms\Components\Textarea::make('comment')
                    ->label('Комментарий')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                // Статус
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает подтверждения',
                        'approved' => 'Одобрена',
                        'rejected' => 'Отклонена',
                        'cancelled' => 'Отменена',
                    ])
                    ->default('pending')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('property.title')
                    ->label('Объект')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('guest_name')
                    ->label('Гость')
                    ->searchable(),

                Tables\Columns\TextColumn::make('guest_phone')
                    ->label('Телефон')
                    ->searchable(),

                Tables\Columns\TextColumn::make('country_code')
                    ->label('Код страны')
                    ->badge(),

                Tables\Columns\TextColumn::make('guest_email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('contact_method')
                    ->label('Способ связи')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'telegram' => 'Telegram',
                        'whatsapp' => 'WhatsApp',
                        'max' => 'MAX',
                        'phone' => 'Звонок',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Заезд')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Выезд')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('adults')
                    ->label('Взрослые')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('children')
                    ->label('Дети')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Стоимость')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => $state instanceof \App\Enums\BookingRequestStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\BookingRequestStatus ? $state->label() : (string) $state),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает подтверждения',
                        'approved' => 'Одобрена',
                        'rejected' => 'Отклонена',
                        'cancelled' => 'Отменена',
                    ]),

                Tables\Filters\SelectFilter::make('property_id')
                    ->label('Объект')
                    ->relationship('property', 'title'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->modalHeading('Удалить заявку?')
                    ->modalDescription('Это действие нельзя отменить. Продолжить?')
                    ->modalSubmitActionLabel('Да, удалить')
                    ->modalCancelActionLabel('Отмена'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->modalHeading('Удалить выбранные заявки?')
                        ->modalSubmitActionLabel('Да, удалить'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookingRequests::route('/'),
            'create' => Pages\CreateBookingRequest::route('/create'),
            'edit' => Pages\EditBookingRequest::route('/{record}/edit'),
        ];
    }

    /**
     * Получение занятых дат для конкретного объекта
     */
    protected static function getDisabledDates(?int $propertyId): array
    {
        if (!$propertyId) {
            return [];
        }

        $property = Property::find($propertyId);
        if (!$property) {
            return [];
        }

        try {
            /** @var AvailabilityService $service */
            $service = app(AvailabilityService::class);
            return $service->getDisabledDates($property);
        } catch (\Exception $e) {
            \Log::error('Ошибка получения занятых дат: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Автоматический расчёт цены через PricingService
     */
    protected static function calculatePrice(Set $set, Get $get): void
    {
        $propertyId = $get('property_id');
        $checkIn = $get('check_in');
        $checkOut = $get('check_out');

        if (!$propertyId || !$checkIn || !$checkOut) {
            return;
        }

        $property = Property::find($propertyId);
        if (!$property) {
            return;
        }

        try {
            $checkInDate = Carbon::parse($checkIn);
            $checkOutDate = Carbon::parse($checkOut);

            /** @var PricingService $pricingService */
            $pricingService = app(PricingService::class);
            $pricing = $pricingService->calculateTotal($property, $checkInDate, $checkOutDate);

            $set('total_price', $pricing['final_total']);
        } catch (\Exception $e) {
            \Log::error('Ошибка расчёта цены: ' . $e->getMessage());
        }
    }
}
