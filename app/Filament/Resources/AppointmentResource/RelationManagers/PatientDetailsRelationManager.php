<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;



class PatientDetailsRelationManager extends RelationManager
{

    protected static string $relationship = 'user';
    protected static ?string $title = 'Patient Information';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        $patient = $this->getOwnerRecord()->user;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Patient Name')->weight('bold'),
                Tables\Columns\TextColumn::make('email')->label('Email')->icon('heroicon-o-envelope'),
                Tables\Columns\TextColumn::make('phone')->label('Phone')->icon('heroicon-o-phone'),
                Tables\Columns\TextColumn::make('date_of_birth')->label('Date of Birth')->date()->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('gender')->label('Gender')->badge(),
                Tables\Columns\TextColumn::make('address')->label('Address'),
                Tables\Columns\TextColumn::make('diseases_count')
                    ->label('Diseases')
                    ->counts('diseases')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('medications_count')
                    ->label('Active Medications')
                    ->counts('medications')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('visits_count')
                    ->label('Visits')
                    ->counts('visits')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading('No patient details')
            ->modifyQueryUsing(function ($query) use ($patient) {
                return $query->where('id', $patient->id);
            });
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit($record): bool
    {
        return false;
    }

    protected function canDelete($record): bool
    {
        return false;
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

