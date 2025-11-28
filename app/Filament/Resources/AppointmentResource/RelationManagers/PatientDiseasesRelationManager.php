<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;



class PatientDiseasesRelationManager extends RelationManager
{

    protected static string $relationship = 'user';
    protected static ?string $title = 'Patient Diseases';
    protected static ?string $recordTitleAttribute = 'name';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $patient = $this->getOwnerRecord()->user;
        return \App\Models\PatientDisease::query()->where('patient_id', $patient->id);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('disease_name')
                    ->required()
                    ->label('Disease Name'),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'resolved' => 'Resolved',
                    ])
                    ->required()
                    ->label('Status'),

                Forms\Components\Select::make('source')
                    ->options([
                        'doctor' => 'Doctor',
                        'patient' => 'Patient',
                        'external' => 'External',
                    ])
                    ->default('doctor')
                    ->label('Source'),
            ]);
    }

    public function table(Table $table): Table
    {
        $patient = $this->getOwnerRecord()->user;

        return $table
            ->query(\App\Models\PatientDisease::query()->where('patient_id', $patient->id))
            ->columns([
                Tables\Columns\TextColumn::make('disease_name')
                    ->searchable()
                    ->label('Disease'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'resolved',
                    ])
                    ->label('Status'),

                Tables\Columns\BadgeColumn::make('source')
                    ->colors([
                        'primary' => 'doctor',
                        'warning' => 'patient',
                        'danger' => 'external',
                    ])
                    ->label('Source'),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->label('Added At'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Disease'),
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

