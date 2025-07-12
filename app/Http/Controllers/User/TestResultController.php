<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TestResult;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;

class TestResultController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $testResults = TestResult::where('patient_id', $user->id)->latest()->get();

        if ($testResults->isEmpty()) {
            return response()->json(['message' => 'No test results found'], 404);
        }

        return response()->json([
            'tests' => $testResults->map(fn($test) => [
                'id' => $test->id,
                'result_date' => $test->result_date,
                'pdf_url' => $this->generatePDF($test),
            ]),
        ]);
    }

    public function generatePDF($record)
    {

        $timestamp = strtotime($record->updated_at);
        $fileName = "Report_{$record->id}_{$timestamp}.pdf";
        $filePath = "pdf_reports/{$fileName}";


        if (!Storage::disk('public')->exists('pdf_reports')) {
            Storage::disk('public')->makeDirectory('pdf_reports');
        }


        $oldFiles = Storage::disk('public')->files('pdf_reports');
        foreach ($oldFiles as $file) {
            if (strpos($file, "Report_{$record->id}_") !== false && $file !== $filePath) {
                Storage::disk('public')->delete($file);
            }
        }


        if (Storage::disk('public')->exists($filePath)) {
            return asset("storage/{$filePath}");
        }


        $mpdf = new Mpdf([
            'default_font' => 'dejavusans',
            'mode' => 'utf-8'
        ]);


        $html = view('pdf.lab_report', compact('record'))->render();
        $mpdf->WriteHTML($html);


        Storage::disk('public')->put($filePath, $mpdf->Output('', 'S'));


        return asset("storage/{$filePath}");
    }
}
