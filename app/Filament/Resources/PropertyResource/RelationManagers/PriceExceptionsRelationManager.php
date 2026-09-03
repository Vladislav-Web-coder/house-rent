<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PriceExceptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceExceptions';
    protected static ?string $title = 'Точечные исключения цен';
    protected static ?string $icon = 'heroicon-o-exclamation-triangle';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Дата')
                    ->required()
                    ->helperText('Конкретная дата, на которую устанавливается особая цена'),

                Forms\Components\TextInput::make('price')
                    ->label('Цена за сутки')
                    ->numeric()
                    ->required()
                    ->prefix('₽'),

                Forms\Components\TextInput::make('reason')
                    ->label('Причина (необязательно)')
                    ->maxLength(255)
                    ->placeholder('Например: "Праздник"'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена/сутки')
                    ->money('RUB'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Причина')
                    ->limit(30),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить исключение'),
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
            ->defaultSort('date', 'desc');
    }
}
