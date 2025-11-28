<?php

namespace App\Services;

use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Appointment;

class PatientReportPdfService
{
    public function generate(Appointment $appointment): string
    {
        $patient = $appointment->user;
        $doctor = $appointment->doctor;

        $timestamp = now()->timestamp;
        $fileName = "Patient_Report_{$patient->id}_{$timestamp}.pdf";
        $filePath = storage_path("app/public/pdf_reports/{$fileName}");

        // Ensure directory exists
        if (!file_exists(storage_path("app/public/pdf_reports"))) {
            mkdir(storage_path("app/public/pdf_reports"), 0755, true);
        }

        // Generate PDF
        $mpdf = new Mpdf([
            'default_font' => 'dejavusans',
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);

        $html = view('pdf.patient_report', compact('appointment', 'patient', 'doctor'))->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($filePath, 'F');

        return $filePath;
    }
}


