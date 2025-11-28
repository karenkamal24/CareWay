<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;


class PatientHabitsRelationManager extends RelationManager
{

    protected static string $relationship = 'user';
    protected static ?string $title = 'Patient Habits';
    protected static ?string $recordTitleAttribute = 'name';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $patient = $this->getOwnerRecord()->user;
        return \App\Models\PatientHabit::query()->where('patient_id', $patient->id);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('smoking')->label('Smoking'),
                Forms\Components\TextInput::make('caffeine')->label('Caffeine Intake'),
                Forms\Components\TextInput::make('exercise')->label('Exercise'),
                Forms\Components\TextInput::make('sleep_hours')->label('Sleep Hours')->numeric(),
                Forms\Components\Textarea::make('notes')->label('Notes'),
            ]);
    }

    public function table(Table $table): Table
    {
        $patient = $this->getOwnerRecord()->user;

        return $table
            ->query(\App\Models\PatientHabit::query()->where('patient_id', $patient->id))
            ->columns([
                Tables\Columns\TextColumn::make('smoking')->label('Smoking'),
                Tables\Columns\TextColumn::make('caffeine')->label('Caffeine'),
                Tables\Columns\TextColumn::make('exercise')->label('Exercise'),
                Tables\Columns\TextColumn::make('sleep_hours')->label('Sleep Hours'),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->limit(50),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Habit'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['patient_id'] = $this->getOwnerRecord()->user_id;
        return $data;
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

