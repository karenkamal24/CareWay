<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers\AvailableAppointmentsRelationManager;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Hospital Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User Email')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department')
                    ->required(),

                TextInput::make('specialization')
                    ->label('Specialization')
                    ->required(),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->required(),

                Textarea::make('description')
                    ->label('Description')
                    ->nullable(),

                TextInput::make('price')
                    ->label('Consultation Fee')
                    ->numeric()
                    ->required(),

                TextInput::make('degree')
                    ->label('Degree')
                    ->nullable(),

                FileUpload::make('image')
                    ->image()
                    ->directory('doctor')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif', 'image/svg+xml'])
                    ->rules(['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:2048'])
                    ->imagePreviewHeight(150)
                    ->columnSpanFull(),

                Toggle::make('status')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                ImageColumn::make('image')
                    ->getStateUsing(fn ($record) => asset('storage/' . $record->image))
                    ->size(50)
                    ->circular(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),

                TextColumn::make('specialization')
                    ->label('Specialization'),

                TextColumn::make('price')
                    ->label('Consultation Fee')
                    ->money('USD'),

                TextColumn::make('rate')
                    ->label('Rate')
                    ->formatStateUsing(fn ($state) => number_format($state, 1)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->label('Filter by Department'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function query(Builder $query): Builder
    {
        return $query->with('roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            AvailableAppointmentsRelationManager::class,
        ];
    }
}
