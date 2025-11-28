<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers\DiseasesRelationManager;
use App\Filament\Resources\PatientResource\RelationManagers\MedicationsRelationManager;
use App\Filament\Resources\PatientResource\RelationManagers\HabitsRelationManager;
use App\Filament\Resources\PatientResource\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\PatientResource\RelationManagers\VisitsRelationManager;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class PatientResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Patients';
    protected static ?string $navigationGroup = 'Clinic';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_type', 'user');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email(),
                TextInput::make('phone'),
                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),
                DatePicker::make('date_of_birth')->label('Date of Birth'),
                TextInput::make('blood_type')->label('Blood Type'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('Phone'),
                TextColumn::make('date_of_birth')->label('Birthdate'),
                TextColumn::make('created_at')->label('Created')->date(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DiseasesRelationManager::class,
            MedicationsRelationManager::class,
            HabitsRelationManager::class,
            AttachmentsRelationManager::class,
            VisitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'view'   => Pages\ViewPatient::route('/{record}'),
            'edit'   => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
