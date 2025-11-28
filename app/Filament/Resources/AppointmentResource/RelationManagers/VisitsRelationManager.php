<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Models\Visit;
use App\Models\Doctor;
use App\Models\PatientMedication;
use App\Models\PatientHabit;
use App\Services\PrescriptionPdfService;
use Filament\Notifications\Notification;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';
    protected static ?string $title = 'Medical Visits';
    protected static ?string $icon = 'heroicon-o-document-text';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('New Visit (Creates a medical record)')
                ->collapsible()
                ->schema([
                    Forms\Components\Repeater::make('medications')
                        ->label('Medications')
                        ->schema([
                            Forms\Components\TextInput::make('medication_name')->required(),
                            Forms\Components\TextInput::make('dose')->placeholder('500mg'),
                            Forms\Components\TextInput::make('frequency')->placeholder('twice daily'),
                            Forms\Components\TextInput::make('duration')->placeholder('7 days'),
                            Forms\Components\Textarea::make('doctor_notes')->rows(2),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Add Medication')
                        ->columns(2),

                    Forms\Components\Repeater::make('habits')
                        ->label('Habits')
                        ->schema([
                            Forms\Components\TextInput::make('habit')
                                ->label('Habit')
                                ->placeholder('Enter habit description...')
                                ->required()
                                ->maxLength(500),
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['habit'] ?? 'Habit Entry')
                        ->addActionLabel('Add Habit')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('diagnosis')
                        ->label('Diagnosis & Notes')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('visit_date')
            ->columns([
                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Visit Date')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d M Y - h:i A') : 'N/A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnosis')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->diagnosis),

                Tables\Columns\BadgeColumn::make('medications_count')
                    ->label('Meds')
                    ->getStateUsing(fn ($record) => $record->medications()->count())
                    ->color('success'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Visit Entry')
                    ->icon('heroicon-o-plus')
                    ->using(function (array $data): Visit {
                        $appointment = $this->ownerRecord; // The Appointment
                        $doctor = Doctor::where('user_id', Auth::id())->first();

                        if (!$doctor) {
                            Notification::make()
                                ->danger()
                                ->title('Doctor not found')
                                ->body('Please make sure you are logged in as a doctor.')
                                ->send();
                            throw new \Exception('Doctor not found');
                        }

                        // Ensure we have at least one field
                        if (
                            empty($data['diagnosis']) &&
                            empty($data['medications']) &&
                            empty($data['habits'])
                        ) {
                            Notification::make()
                                ->warning()
                                ->title('No data entered')
                                ->body('Add diagnosis, medication or habits to create a visit.')
                                ->send();
                            throw new \Exception('No data entered');
                        }

                        // Create visit record with doctor_id
                        $visit = Visit::create([
                            'patient_id' => $appointment->user_id,
                            'doctor_id'  => $doctor->id,
                            'visit_date' => now(),
                            'diagnosis'  => $data['diagnosis'] ?? null,
                            'notes'      => $data['diagnosis'] ?? null,
                        ]);

                        // Save medications
                        foreach ($data['medications'] ?? [] as $med) {
                            if (!empty($med['medication_name'])) {
                                PatientMedication::create([
                                    'patient_id'      => $appointment->user_id,
                                    'doctor_id'       => $doctor->id,
                                    'visit_id'        => $visit->id,
                                    'medication_name' => $med['medication_name'],
                                    'dose'            => $med['dose'] ?? null,
                                    'frequency'       => $med['frequency'] ?? null,
                                    'duration'        => $med['duration'] ?? null,
                                    'doctor_notes'    => $med['doctor_notes'] ?? null,
                                    'is_active'       => true,
                                    'source'          => 'doctor',
                                    'start_date'      => now(),
                                ]);
                            }
                        }

                        // Save habits (create one record per habit entry)
                        if (!empty($data['habits'])) {
                            foreach ($data['habits'] as $habitData) {
                                if (empty($habitData['habit'])) {
                                    continue;
                                }

                                // Save each habit as a separate record using the notes field
                                PatientHabit::create([
                                    'patient_id' => $appointment->user_id,
                                    'notes' => $habitData['habit'],
                                ]);
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Visit Created Successfully')
                            ->send();

                        return $visit;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('prescription')
                    ->label('Download Prescription')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(function (Visit $record) {
                        $pdfService = new PrescriptionPdfService();
                        $pdf = $pdfService->generatePrescription($record);

                        return response()->streamDownload(
                            fn () => print($pdf),
                            "Prescription_{$record->id}.pdf"
                        );
                    }),
            ]);
    }

    public static function getResourcePermission(): ?string
    {
        return 'appointment';
    }
}
