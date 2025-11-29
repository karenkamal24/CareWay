<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SymptomAnalyzerService;

class AIController extends Controller
{
    /**
     * استلام الأعراض من المستخدم وإرجاع الأقسام + الدكاترة
     */
    public function suggestDoctors(Request $request, SymptomAnalyzerService $service)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'symptoms' => 'required|string|min:3',
        ], [
            'symptoms.required' => 'برجاء إدخال الأعراض.',
            'symptoms.string'   => 'الأعراض يجب أن تكون نصاً.',
            'symptoms.min'      => 'من فضلك اكتب عرض واحد على الأقل بشكل واضح.',
        ]);

        // استدعاء الخدمة
        $result = $service->analyzeSymptoms($request->input('symptoms'));

        // إرجاع JSON بالعربي بدون Unicode
        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
