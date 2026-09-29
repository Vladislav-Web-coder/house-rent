<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PriceByDayOfWeeksRelationManager extends RelationManager
{
    protected static string $relationship = 'priceByDayOfWeeks';
    protected static ?string $title = 'Цены по дням недели';
    protected static ?string $icon = 'heroicon-o-calendar';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('day_of_week')
                    ->label('День недели')
                    ->options([
                        1 => 'Понедельник',
                        2 => 'Вторник',
                        3 => 'Среда',
                        4 => 'Четверг',
                        5 => 'Пятница',
                        6 => 'Суббота',
                        0 => 'Воскресенье',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Цена за сутки')
                    ->numeric()
                    ->required()
                    ->prefix('₽'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Активна')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('День недели')
                    ->formatStateUsing(fn ($state): string => match ((int)$state) {
                        0 => 'Воскресенье',
                        1 => 'Понедельник',
                        2 => 'Вторник',
                        3 => 'Среда',
                        4 => 'Четверг',
                        5 => 'Пятница',
                        6 => 'Суббота',
                        default => 'Неизвестно',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена/сутки')
                    ->money('RUB'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность')
                    ->trueLabel('Активные')
                    ->falseLabel('Неактивные'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить цену для дня'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('day_of_week');
    }
}
