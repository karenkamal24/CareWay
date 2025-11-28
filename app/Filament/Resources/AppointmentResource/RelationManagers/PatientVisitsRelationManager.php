<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Visit;
use Filament\Actions\Action;


class PatientVisitsRelationManager extends RelationManager
{

    protected static string $relationship = 'user';
    protected static ?string $title = 'Patient Visits';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        $patient = $this->getOwnerRecord()->user;

        return $table
            ->query(Visit::query()->where('patient_id', $patient->id)->latest('visit_date'))
            ->columns([
                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Visit Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('symptoms')
                    ->label('Symptoms')
                    ->limit(50),
                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnosis')
                    ->limit(50),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Visit $record) {
                        return $this->downloadVisitPdf($record);
                    }),
            ])
            ->bulkActions([]);
    }

    protected function downloadVisitPdf(Visit $visit)
    {
        $patient = $visit->patient;
        $doctor = $visit->doctor;

        $mpdf = new \Mpdf\Mpdf([
            'default_font' => 'dejavusans',
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);

        $html = view('pdf.visit_report', compact('visit', 'patient', 'doctor'))->render();
        $mpdf->WriteHTML($html);

        $fileName = "Visit_Report_{$visit->id}_" . $visit->visit_date->format('Y-m-d') . ".pdf";

        return response()->streamDownload(
            fn() => print($mpdf->Output('', 'S')),
            $fileName
        );
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
