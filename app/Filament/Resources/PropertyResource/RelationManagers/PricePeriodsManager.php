<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PricePeriodsRelationManager extends RelationManager
{
    protected static string $relationship = 'pricePeriods';
    protected static ?string $title = 'Сезонные цены';
    protected static ?string $icon = 'heroicon-o-calendar-days';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название периода')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Например: "Новогодние праздники"'),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Начало периода')
                    ->required(),

                Forms\Components\DatePicker::make('end_date')
                    ->label('Конец периода')
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Цена за сутки')
                    ->numeric()
                    ->required()
                    ->prefix('₽'),

                Forms\Components\TextInput::make('priority')
                    ->label('Приоритет')
                    ->numeric()
                    ->default(10)
                    ->helperText('Чем выше число, тем выше приоритет при пересечении периодов'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Начало')
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Конец')
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена/сутки')
                    ->money('RUB'),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Приоритет'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
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
                    ->label('Добавить сезонный период'),
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
            ->defaultSort('priority', 'desc');
    }
}
