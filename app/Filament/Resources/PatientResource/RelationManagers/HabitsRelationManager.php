<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class HabitsRelationManager extends RelationManager
{
    protected static string $relationship = 'habits';
    protected static ?string $title = 'Habits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('smoking')->label('Smoking'),
                Forms\Components\TextInput::make('caffeine')->label('Caffeine Intake'),
                Forms\Components\TextInput::make('exercise')->label('Exercise'),
                Forms\Components\TextInput::make('sleep_hours')->label('Sleep Hours'),
                Forms\Components\Textarea::make('notes')->label('Notes'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('smoking')->label('Smoking'),
                Tables\Columns\TextColumn::make('caffeine')->label('Caffeine'),
                Tables\Columns\TextColumn::make('exercise')->label('Exercise'),
                Tables\Columns\TextColumn::make('sleep_hours')->label('Sleep'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Habit'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
