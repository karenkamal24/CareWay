<?php

namespace App\Filament\Resources\Lab;

use Filament\Tables\Filters\Filter;
use App\Filament\Resources\Lab\TestResultResource\Pages;
use App\Models\TestResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class TestResultResource extends Resource
{
    protected static ?string $model = TestResult::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard';
    protected static ?string $navigationGroup = 'Lab Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('patient_id')
                    ->relationship('patient', 'name')
                    ->required()
                    ->label('Patient'),

                TextInput::make('test_name')->required()->maxLength(255),
                TextInput::make('result')->required(),
                TextInput::make('unit')->required(),
                TextInput::make('range')->nullable(),
                TextInput::make('note')->nullable(),

                Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->nullable()
                    ->label('Doctor'),

                DatePicker::make('test_date')->required(),
                DatePicker::make('result_date')->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.name')->label('Patient')->searchable(),
                TextColumn::make('test_name')->searchable(),
                TextColumn::make('result')->searchable(),
                TextColumn::make('unit')->searchable(),
                TextColumn::make('range')->searchable(),
                TextColumn::make('note')->searchable(),

                TextColumn::make('test_date')
                    ->label('Test Date')
                    ->date(),

                TextColumn::make('result_date')
                    ->label('Result Date')
                    ->date(),

                TextColumn::make('doctor.full_name')->label('Doctor')->searchable(),
            ])
            ->filters([
                Filter::make('patient_name')
                    ->form([
                        TextInput::make('name')->label('Patient Name'),
                    ])
                    ->query(fn ($query, array $data) =>
                        isset($data['name']) && $data['name'] !== ''
                            ? $query->whereHas('patient', fn ($q) =>
                                $q->where('full_name', 'like', '%' . $data['name'] . '%')
                            )
                            : $query
                    )
                    ->indicateUsing(fn ($data) => isset($data['name']) && $data['name'] !== '' ? 'Filtering by Patient Name' : null),

                Filter::make('doctor_name')
                    ->form([
                        TextInput::make('name')->label('Doctor Name'),
                    ])
                    ->query(fn ($query, array $data) =>
                        isset($data['name']) && $data['name'] !== ''
                            ? $query->whereHas('doctor', fn ($q) =>
                                $q->where('full_name', 'like', '%' . $data['name'] . '%')
                            )
                            : $query
                    )
                    ->indicateUsing(fn ($data) => isset($data['name']) && $data['name'] !== '' ? 'Filtering by Doctor Name' : null),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestResults::route('/'),
            'create' => Pages\CreateTestResult::route('/create'),
            'edit' => Pages\EditTestResult::route('/{record}/edit'),
        ];
    }
}
