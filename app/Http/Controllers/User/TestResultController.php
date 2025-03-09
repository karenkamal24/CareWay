<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TestResult;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class TestResultController extends Controller
{
    public function index(Request $request)
    {
      
        $user = $request->user();
        $testResults = TestResult::where('patient_id', $user->id)->get();

        return response()->json([
            'tests' => $testResults->map(function ($test) {
                return [
                    'id' => $test->id,
                    'test_name' => $test->test_name,
                    'result' => $test->result,
                    'unit' => $test->unit,
                    'range' => $test->range,
                    'test_date' => $test->test_date,
                    'result_date' => $test->result_date,
                    'doctor' => optional($test->doctor)->name ?? 'N/A',
                    'pdf_url' => $this->generatePDF($test), 
                ];
            }),
        ]);
    }


    public function generatePDF($record)
    {
        $fileName = "Lab_Report_{$record->id}.pdf";
        $filePath = "pdf_reports/{$fileName}";

       
        if (Storage::disk('public')->exists($filePath)) {
            return asset("storage/{$filePath}");
        }


        $pdf = Pdf::loadView('pdf.lab_report', ['record' => $record]);
        Storage::disk('public')->put($filePath, $pdf->output());

        return asset("storage/{$filePath}");
    }
}
