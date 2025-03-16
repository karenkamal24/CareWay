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
use Mpdf\Mpdf;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TestResultNotification;

class TestResultResource extends Resource
{
    protected static ?string $model = TestResult::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard';
    protected static ?string $navigationGroup = 'Lab Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('patient_id')
                ->relationship('patient', 'name')
                ->required()
                ->label('Patient')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $patient = \App\Models\User::find($state);
                    $set('patient_name', $patient?->name);
                    $set('patient_email', $patient?->email);
                }),

            Select::make('doctor_id')
                ->relationship('doctor', 'name')
                ->nullable()
                ->label('Doctor')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $doctor = \App\Models\Doctor::find($state);
                    $set('doctor_name', $doctor?->name);
                    $set('doctor_email', $doctor?->email);
                }),

            TextInput::make('patient_name')->required()->label('Patient Name'),
            TextInput::make('doctor_name')->required()->label('Doctor Name'),
            TextInput::make('patient_email')->email()->nullable()->label('Patient Email'),
            TextInput::make('age')->numeric()->nullable()->label('Age'),
            TextInput::make('doctor_email')->email()->nullable()->label('Doctor Email'),
            DatePicker::make('test_date')->required(),
            DatePicker::make('result_date')->required(),

            Section::make('Test Details')->schema([
                Repeater::make('tests')
                    ->label('Tests')
                    ->schema([
                        TextInput::make('test')->label('Test Name')->required(),
                        Repeater::make('results')
                            ->label('Results')
                            ->schema([
                                TextInput::make('result')->label('Result')->required(),
                                TextInput::make('unit')->label('Unit')->required(),
                            ])
                            ->addable()
                            ->reorderable()
                            ->deletable()
                            ->columns(2)
                            ->default([]),

                        Repeater::make('ranges')
                            ->label('Ranges')
                            ->schema([
                                TextInput::make('test')->label('Test Name')->required(),
                                TextInput::make('description')->label('Description')->nullable(),
                            ])
                            ->addable()
                            ->reorderable()
                            ->deletable()
                            ->columns(2)
                            ->default([]),
                    ])
                    ->addable()
                    ->reorderable()
                    ->deletable()
                    ->columns(1)
                    ->default([]),
            ]),

            TextInput::make('note')->required()->label('Comment'),
            TextInput::make('total_cost')->numeric()->nullable()->label('Total Cost'),
            TextInput::make('amount_paid')->numeric()->default(0)->label('Amount Paid'),

            Select::make('status')
                ->options([
                    'unpaid' => 'Unpaid',
                    'partial' => 'Partial',
                    'paid' => 'Paid',
                ])
                ->default('unpaid')
                ->label('Payment Status'),

            Select::make('test_status')
                ->label('Test Status')
                ->options([
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ])
                ->default('pending')
                ->required(),
        ]);
    }
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.name')->label('Patient')->searchable(),
                TextColumn::make('patient_email')->label('Patient Email')->searchable(),
                TextColumn::make('doctor.name')->label('Doctor')->searchable(),
                TextColumn::make('doctor_email')->label('Doctor Email')->searchable(),
                TextColumn::make('age')->label('Age'),
                TextColumn::make('test_date')->label('Test Date')->date(),
                TextColumn::make('result_date')->label('Result Date')->date(),
                TextColumn::make('total_cost')->label('Total Cost')->money('USD'),
                TextColumn::make('amount_paid')->label('Amount Paid')->money('USD'),
                TextColumn::make('status')->label('Payment Status')->badge(),
                TextColumn::make('test_status')->label('Test Status')->badge(),
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
                    ),
    
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
                    ),
            ])
            ->actions([
                Action::make('downloadPDF')
                    ->label('Download PDF')
                    ->action(fn ($record) => static::generatePDF($record))
                    ->color('success'),
    
                Action::make('sendNotification')
                    ->label('Send Notification')
                    ->action(fn ($record) => static::sendNotification($record))
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'paid' && $record->test_status === 'completed'),
            ]);
    }
    
    public static function sendNotification($record)
    {
        if ($record->status === 'paid' && $record->test_status === 'completed') {
            Notification::send($record->patient, new TestResultNotification($record));
        }
    }
    
    public static function generatePDF($record)
    {
        $mpdf = new Mpdf([
            'default_font' => 'dejavusans',
            'mode' => 'utf-8'
        ]);

        $html = view('pdf.lab_report', compact('record'))->render();
        $mpdf->WriteHTML($html);

        $fileName = "Report_{$record->id}.pdf";

        return response()->streamDownload(
            fn() => print($mpdf->Output('', 'S')),
            $fileName
        );
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
