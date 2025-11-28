<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class MedicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'medications';
    protected static ?string $title = 'Medications';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('medication_name')
                    ->required()
                    ->label('Medication Name'),

                Forms\Components\TextInput::make('dose')
                    ->label('Dose'),

                Forms\Components\TextInput::make('frequency')
                    ->label('Frequency'),

                Forms\Components\TextInput::make('duration')
                    ->label('Duration'),

                Forms\Components\Select::make('source')
                    ->label('Source')
                    ->options([
                        'doctor' => 'Doctor',
                        'patient' => 'Patient',
                        'external' => 'External',
                    ])
                    ->default('doctor'),

                Forms\Components\Textarea::make('doctor_notes')
                    ->label("Doctor Notes"),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('medication_name')
                    ->label('Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('dose')->label('Dose'),
                Tables\Columns\TextColumn::make('frequency')->label('Frequency'),

                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Stopped'),

                Tables\Columns\BadgeColumn::make('source')
                    ->colors([
                        'primary' => 'doctor',
                        'warning' => 'patient',
                        'danger' => 'external',
                    ])
                    ->label('Source'),
            ])

            ->headerActions([
                Tables\Actions\CreateAction::make()->label("Add Medication"),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                // Stop medication
                Tables\Actions\Action::make('stop')
                    ->label('Stop')
                    ->visible(fn ($record) => $record->is_active)
                    ->color('danger')
                    ->action(function ($record) {
                        $record->update([
                            'is_active' => false,
                            'end_date' => now(),
                        ]);
                    }),

                // Activate medication
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->visible(fn ($record) => ! $record->is_active)
                    ->color('success')
                    ->action(function ($record) {
                        $record->update([
                            'is_active' => true,
                            'start_date' => now(),
                        ]);
                    }),

                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
