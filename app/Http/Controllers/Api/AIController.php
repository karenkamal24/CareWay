<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SymptomAnalyzerService;

class AIController extends Controller
{
    /**
     * API لاستقبال الأعراض واقتراح الأقسام والأطباء حسب أقرب مواعيد
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

        // تشغيل الخدمة
        $result = $service->analyzeSymptoms($request->input('symptoms'));

        // لو مفيش أطباء عندهم مواعيد قريبة
        if ($result['success'] && empty($result['suggested_doctors'])) {
            $result['message'] = 'تم تحليل الأعراض بنجاح، ولكن لا يوجد مواعيد متاحة حالياً لهؤلاء الأطباء.';
        }

        // إرجاع JSON بالعربية بدون Unicode مشفر
        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
