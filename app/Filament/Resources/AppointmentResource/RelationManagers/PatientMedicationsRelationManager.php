<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\PatientMedication;


class PatientMedicationsRelationManager extends RelationManager
{

    protected static string $relationship = 'user';
    protected static ?string $title = 'Patient Medications';
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        $patient = $this->getOwnerRecord()->user;

        return $table
            ->query(PatientMedication::query()->where('patient_id', $patient->id))
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
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'doctor' => 'Doctor',
                        'patient' => 'Patient',
                        'external' => 'External',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Stopped'),
            ])

            ->headerActions([
                Tables\Actions\CreateAction::make()->label("Add Medication"),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // Stop medication
                Tables\Actions\Action::make('stop')
                    ->label('Stop')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn ($record) => $record->is_active)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_active' => false,
                            'end_date' => now(),
                        ]);
                    })
                    ->successNotificationTitle('Medication stopped successfully'),

                // Activate medication
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => ! $record->is_active)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_active' => true,
                            'start_date' => now(),
                        ]);
                    })
                    ->successNotificationTitle('Medication activated successfully'),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

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


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['patient_id'] = $this->getOwnerRecord()->user_id;
        $data['doctor_id'] = $this->getOwnerRecord()->doctor_id;
        return $data;
    }

    protected function canCreate(): bool
    {
        return true;
    }

    protected function canEdit($record): bool
    {
        return true;
    }

    protected function canDelete($record): bool
    {
        return true;
    }
    public static function getResourcePermission(): ?string
{
    return 'appointment';
}

public static function canViewForRecord($record, $user): bool
{
    return true;
}

}

