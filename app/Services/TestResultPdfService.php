<?php
namespace App\Services;

use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use App\Models\TestResult;

class TestResultPdfService
{
    public function generate(TestResult $record): string
    {
        $timestamp = strtotime($record->updated_at);
        $fileName = "Report_{$record->id}_{$timestamp}.pdf";
        $filePath = "pdf_reports/{$fileName}";

        // Ensure directory exists
        if (!Storage::disk('public')->exists('pdf_reports')) {
            Storage::disk('public')->makeDirectory('pdf_reports');
        }

        // Delete old versions
        foreach (Storage::disk('public')->files('pdf_reports') as $file) {
            if (str_contains($file, "Report_{$record->id}_") && $file !== $filePath) {
                Storage::disk('public')->delete($file);
            }
        }

        // If file exists, return URL
        if (Storage::disk('public')->exists($filePath)) {
            return asset("storage/{$filePath}");
        }

        // Generate PDF
        $mpdf = new Mpdf([
            'default_font' => 'dejavusans',
            'mode' => 'utf-8',
        ]);
        $html = view('pdf.lab_report', compact('record'))->render();
        $mpdf->WriteHTML($html);
        Storage::disk('public')->put($filePath, $mpdf->Output('', 'S'));

        return asset("storage/{$filePath}");
    }
}
