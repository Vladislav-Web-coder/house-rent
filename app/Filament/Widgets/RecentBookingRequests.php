<?php

namespace App\Filament\Widgets;

use App\Enums\BookingRequestStatus;
use App\Filament\Resources\BookingRequestResource;
use App\Models\BookingRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentBookingRequests extends BaseWidget
{
    protected static ?string $heading = 'Последние заявки';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $defaultPaginationPageOption = '5';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BookingRequestResource::getEloquentQuery()
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('property.title')
                    ->label('Объект')
                    ->limit(25),

                Tables\Columns\TextColumn::make('guest_name')
                    ->label('Гость')
                    ->limit(20)
                    ->searchable(),

                Tables\Columns\TextColumn::make('guest_phone')
                    ->label('Телефон')
                    ->limit(18),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Заезд')
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Выезд')
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => $state instanceof BookingRequestStatus ? $state->color() : (string) $state)
                    ->formatStateUsing(fn ($state) => $state instanceof BookingRequestStatus ? $state->label() : (string) $state),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Открыть')
                    ->icon('heroicon-o-eye')
                    ->url(fn (BookingRequest $record): string => BookingRequestResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])
            ->emptyStateHeading('Нет заявок')
            ->emptyStateDescription('Новые заявки будут появляться здесь');
    }
}
