<?php

namespace App\Services;

use Mpdf\Mpdf;
use App\Models\Visit;
use App\Models\PatientMedication;
use App\Models\PatientHabit;

class PrescriptionPdfService
{
    /**
     * Generate prescription PDF for a visit
     */
    public function generatePrescription(Visit $visit): string
    {
        // Load relationships
        $visit->load(['patient', 'doctor.department']);
        $patient = $visit->patient;
        $doctor = $visit->doctor;

        // Get medications for this visit
        // Use visit_date if available, otherwise use created_at
        $visitDate = $visit->visit_date ?? $visit->created_at;

        // Get medications created on the same day as the visit
        // This includes medications from the Repeater that were saved with this visit
        $medications = PatientMedication::where('patient_id', $patient->id)
            ->where('doctor_id', $visit->doctor_id)
            ->where(function($query) use ($visitDate) {
                // Match by start_date (if set) or created_at (for medications created in this visit)
                $query->whereDate('start_date', $visitDate->format('Y-m-d'))
                      ->orWhereDate('created_at', $visitDate->format('Y-m-d'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get patient habits
        $habits = PatientHabit::where('patient_id', $patient->id)->first();

        $mpdf = new Mpdf([
            'default_font' => 'dejavusans',
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);

        $html = view('pdf.prescription', compact('visit', 'patient', 'doctor', 'medications', 'habits'))->render();
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
}

