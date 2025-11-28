<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use App\Models\Doctor;
use App\Models\PatientMedication;
use App\Models\PatientHabit;
use App\Models\Visit;
use App\Services\PrescriptionPdfService;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    public array $medications = [];
    public array $habits = [];
    public ?string $diagnosis = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadPrescription')
                ->label('Download Prescription')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->action(function () {
                    return $this->downloadPrescription();
                })
                ->visible(function () {
                    // Show only if there's at least one visit
                    $latestVisit = Visit::where('patient_id', $this->record->user_id)
                        ->where('doctor_id', $this->record->doctor_id)
                        ->latest('visit_date')
                        ->first();
                    return $latestVisit !== null;
                }),
            Actions\Action::make('downloadPdf')
                ->label('Download Patient Report')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return $this->downloadPatientReport();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function downloadPrescription()
    {
        // Get the latest visit for this appointment
        $latestVisit = Visit::where('patient_id', $this->record->user_id)
            ->where('doctor_id', $this->record->doctor_id)
            ->latest('visit_date')
            ->first();

        if (!$latestVisit) {
            Notification::make()
                ->title('No Prescription Found')
                ->body('No visit found for this appointment. Please create a visit first by adding medications, habits, or diagnosis.')
                ->warning()
                ->send();
            return;
        }

        $pdfService = new PrescriptionPdfService();
        $pdfContent = $pdfService->generatePrescription($latestVisit);

        $fileName = "Prescription_{$latestVisit->id}_" . $latestVisit->visit_date->format('Y-m-d') . ".pdf";

        return response()->streamDownload(
            fn() => print($pdfContent),
            $fileName
        );
    }

    protected function downloadPatientReport()
    {
        $appointment = $this->record;
        $patient = $appointment->user;
        $doctor = $appointment->doctor;

        $mpdf = new \Mpdf\Mpdf([
            'default_font' => 'dejavusans',
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);

        $html = view('pdf.patient_report', compact('appointment', 'patient', 'doctor'))->render();
        $mpdf->WriteHTML($html);

        $fileName = "Patient_Report_{$patient->id}_" . now()->format('Y-m-d') . ".pdf";

        return response()->streamDownload(
            fn() => print($mpdf->Output('', 'S')),
            $fileName
        );
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function mutateFormSchemaUsing(array $schema): array
    {
        // Add medical entry fields for doctors only
        $user = Auth::user();
        $isDoctor = false;


        if ($isDoctor) {
            $schema[] = Section::make('Medical Entry (Creates a Visit)')
                ->description('Add medications, habits, and diagnosis. A visit will be created with today\'s date.')
                ->schema([
                    Repeater::make('medications')
                        ->label('Medications')
                        ->schema([
                            TextInput::make('medication_name')
                                ->label('Medication Name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('dose')
                                ->label('Dose')
                                ->placeholder('e.g., 500mg')
                                ->maxLength(100),
                            TextInput::make('frequency')
                                ->label('Frequency')
                                ->placeholder('e.g., twice daily, once daily')
                                ->maxLength(100),
                            TextInput::make('duration')
                                ->label('Duration')
                                ->placeholder('e.g., 30 days, 2 weeks')
                                ->maxLength(100),
                            Textarea::make('doctor_notes')
                                ->label('Notes')
                                ->rows(2)
                                ->maxLength(500),
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['medication_name'] ?? null)
                        ->addActionLabel('Add Medication')
                        ->columnSpanFull(),

                    Repeater::make('habits')
                        ->label('Habits')
                        ->schema([
                            TextInput::make('habit')
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

                    Textarea::make('diagnosis')
                        ->label('Diagnosis')
                        ->placeholder('Enter diagnosis and medical notes...')
                        ->rows(4)
                        ->columnSpanFull(),
                ]);
        }

        return $schema;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store medical data before saving
        $this->medications = $data['medications'] ?? [];
        $this->habits = $data['habits'] ?? [];
        $this->diagnosis = $data['diagnosis'] ?? null;

        // Remove from form data (these are not part of Appointment model)
        unset($data['medications'], $data['habits'], $data['diagnosis']);

        return $data;
    }

    protected function afterSave(): void
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor || $doctor->id !== $this->record->doctor_id) {
            return;
        }

        $hasData = !empty($this->medications) || !empty($this->habits) || !empty($this->diagnosis);

        if (!$hasData) {
            return;
        }

        try {
            $patientId = $this->record->user_id;
            $visitDate = now();

            // Create visit record
            $visit = Visit::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctor->id,
                'visit_date' => $visitDate,
                'diagnosis' => $this->diagnosis,
                'notes' => $this->diagnosis,
            ]);

            $medicationsCount = 0;
            $habitsCount = 0;

            // Save medications
            if (!empty($this->medications)) {
                foreach ($this->medications as $medData) {
                    if (empty($medData['medication_name'])) {
                        continue;
                    }

                    PatientMedication::create([
                        'patient_id' => $patientId,
                        'doctor_id' => $doctor->id,
                        'medication_name' => $medData['medication_name'],
                        'dose' => $medData['dose'] ?? null,
                        'frequency' => $medData['frequency'] ?? null,
                        'duration' => $medData['duration'] ?? null,
                        'doctor_notes' => $medData['doctor_notes'] ?? null,
                        'is_active' => true,
                        'source' => 'doctor',
                        'start_date' => $visitDate,
                    ]);
                    $medicationsCount++;
                }
            }

            // Save habits (create one record per habit entry)
            if (!empty($this->habits)) {
                foreach ($this->habits as $habitData) {
                    if (empty($habitData['habit'])) {
                        continue;
                    }

                    // Save each habit as a separate record using the notes field
                    PatientHabit::create([
                        'patient_id' => $patientId,
                        'notes' => $habitData['habit'],
                    ]);
                    $habitsCount++;
                }
            }

            // Show success notification
            $parts = [];
            $parts[] = "1 visit created";
            if ($medicationsCount > 0) $parts[] = "{$medicationsCount} medication(s)";
            if ($habitsCount > 0) $parts[] = "{$habitsCount} habit(s)";

            Notification::make()
                ->title('Medical Data Saved')
                ->body(implode(', ', $parts))
                ->success()
                ->send();

            // Clear the data after saving
            $this->medications = [];
            $this->habits = [];
            $this->diagnosis = null;

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to save medical data: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
