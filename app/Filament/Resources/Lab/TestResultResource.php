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
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\KeyValue;
use Illuminate\Support\Facades\Storage;

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
                TextInput::make('result')->required()->maxLength(255),
                TextInput::make('unit')->maxLength(50),
                TextInput::make('range')->maxLength(50),
                TextInput::make('age'),

                KeyValue::make('variables')
                    ->keyLabel('Variable Name')
                    ->valueLabel('Value')
                    ->addButtonLabel('Add New Variable')
                    ->nullable(),

                DatePicker::make('test_date')->required(),
                DatePicker::make('result_date')->required(),

                Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->nullable()
                    ->label('Doctor'),

                TextInput::make('note')->nullable(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.name')->label('Patient')->searchable(),
                TextColumn::make('test_name')->searchable(),
                TextColumn::make('result')->label('Result'),
                TextColumn::make('unit')->label('Unit'),
                TextColumn::make('range')->label('Range'),
                TextColumn::make('test_date')->label('Test Date')->date(),
                TextColumn::make('result_date')->label('Result Date')->date(),
                TextColumn::make('doctor.name')->label('Doctor')->searchable(),
                TextColumn::make('age'),
            ])
            ->actions([
                Action::make('downloadPDF')
                    ->label('Download PDF')
                    ->url(fn ($record) => static::generatePDF($record), true) // ✅ يفتح الرابط مباشرة
                    ->openUrlInNewTab() // ✅ يفتح الرابط في نافذة جديدة
                    ->color('success'),
            ])
            ->filters([
                Filter::make('patient_name')
                    ->form([
                        TextInput::make('name')->label('Patient Name'),
                    ])
                    ->query(fn ($query, array $data) =>
                        isset($data['name']) && $data['name'] !== ''
                            ? $query->whereHas('patient', fn ($q) =>
                                $q->where('name', 'like', '%' . $data['name'] . '%')
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
                                $q->where('name', 'like', '%' . $data['name'] . '%')
                            )
                            : $query
                    )
                    ->indicateUsing(fn ($data) => isset($data['name']) && $data['name'] !== '' ? 'Filtering by Doctor Name' : null),
            ]);
    }

    public static function generatePDF($record)
    {
       
        $pdf = Pdf::loadView('pdf.lab_report', ['record' => $record]);

       
        $fileName = "Lab_Report_{$record->id}.pdf";
        $filePath = "pdf_reports/{$fileName}";

    
        Storage::disk('public')->put($filePath, $pdf->output());

       
        return asset("storage/{$filePath}");
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
