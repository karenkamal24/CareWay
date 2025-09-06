<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AvailableAppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'availableAppointments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
           Forms\Components\Select::make('day_of_week')
    ->label('Day of Week')
    ->options([
        'Sunday' => 'Sunday',
        'Monday' => 'Monday',
        'Tuesday' => 'Tuesday',
        'Wednesday' => 'Wednesday',
        'Thursday' => 'Thursday',
        'Friday' => 'Friday',
        'Saturday' => 'Saturday',
    ])
    ->required(),


                Forms\Components\TimePicker::make('start_time')
                    ->label('Start Time')
                    ->required(),

                Forms\Components\TimePicker::make('end_time')
                    ->label('End Time')
                    ->required(),

                Forms\Components\TextInput::make('capacity')
                    ->numeric()
                    ->default(0)
                    ->label('Capacity'),

                Forms\Components\TextInput::make('booked_count')
                    ->numeric()
                    ->default(0)
                    ->label('Booked Count'),

                Forms\Components\Toggle::make('is_recurring')
                    ->label('Recurring')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time'),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity'),

                Tables\Columns\TextColumn::make('booked_count')
                    ->label('Booked'),

                Tables\Columns\BooleanColumn::make('is_recurring')
                    ->label('Recurring')
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
