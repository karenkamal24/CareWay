<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TestResult;
use App\Services\TestResultPdfService;
use App\Helpers\ApiResponseHelper;

class TestResultController extends Controller
{
    protected TestResultPdfService $pdfService;

    public function __construct(TestResultPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponseHelper::error('Unauthorized', 401);
        }

        $testResults = TestResult::where('patient_id', $user->id)->latest()->get();

        if ($testResults->isEmpty()) {
            return ApiResponseHelper::error('No test results found', 404);
        }

        $data = $testResults->map(fn($test) => [
            'id' => $test->id,
            'result_date' => $test->result_date,
            'pdf_url' => $this->pdfService->generate($test),
        ]);

        return ApiResponseHelper::success('Test results retrieved successfully.', $data);
    }
}
