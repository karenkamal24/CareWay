<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Department;
use App\Models\Doctor;

class SymptomAnalyzerService
{
    /**
     * تحليل الأعراض باستخدام AI واقتراح الأقسام والأطباء
     */
    public function analyzeSymptoms(string $symptoms): array
    {
        try {
            // جلب الأقسام من قاعدة البيانات
            $departments = Department::where('status', true)->pluck('name')->toArray();

            // بناء prompt للذكاء الاصطناعي
            $prompt = $this->buildPrompt($symptoms, $departments);

            // استدعاء Groq API
            $aiResponse = $this->callGroqAPI($prompt);

            // استخراج الأقسام المقترحة
            $suggestedDepartments = $this->parseAIResponse($aiResponse, $departments);

            // fallback في حالة عدم اكتشاف قسم
            if (empty($suggestedDepartments)) {
                $suggestedDepartments = $this->fallbackKeywordMatching($symptoms, $departments);
            }

            // جلب الأطباء لهذه الأقسام
            $suggestedDoctors = $this->getDoctorsByDepartments($suggestedDepartments);

            return [
                'success' => true,
                'message' => 'تم تحليل الأعراض بنجاح',
                'symptoms' => $symptoms,
                'suggested_departments' => $suggestedDepartments,
                'suggested_doctors' => $suggestedDoctors,
                'total_doctors' => count($suggestedDoctors)
            ];

        } catch (\Exception $e) {
            Log::error('Error in analyzeSymptoms: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحليل الأعراض'
            ];
        }
    }

    /**
     * بناء prompt مخصص للـ AI
     */
    private function buildPrompt(string $symptoms, array $departments): string
    {
        $departmentsList = implode(', ', $departments);

        return "أنت مساعد طبي ذكي. المستخدم يعاني من الأعراض التالية: {$symptoms}

الأقسام الطبية المتاحة:
{$departmentsList}

حلل الأعراض واقترح اسم القسم الطبي المناسب فقط، بدون شرح.
أجب بقائمة أقسام مفصولة بفواصل.";
    }

    /**
     * استدعاء Groq API
     */
    private function callGroqAPI(string $prompt): string
    {
        try {
            // قراءة المفتاح من environment مباشرة بدون config
            $apiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');

            if (empty($apiKey)) {
                Log::warning('Groq API Key is missing. Using fallback mode.');
                return ''; // هذا يؤدي لتشغيل fallback
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        ['role' => 'system', 'content' => 'أجب بالعربية فقط.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 200,
                ]);

            return $response->json()['choices'][0]['message']['content'] ?? '';

        } catch (\Exception $e) {
            Log::error('Groq API Error: ' . $e->getMessage());
            return ''; // fallback mode
        }
    }

    /**
     * استخراج الأقسام من رد الـ AI
     */
    private function parseAIResponse(string $aiResponse, array $availableDepartments): array
    {
        if (empty($aiResponse)) return [];

        $suggested = [];
        foreach ($availableDepartments as $dept) {
            if (stripos($aiResponse, $dept) !== false) {
                $suggested[] = $dept;
            }
        }
        return array_unique($suggested);
    }

    /**
     * fallback في حالة عدم توفر AI
     */
    private function fallbackKeywordMatching(string $symptoms, array $departments): array
    {
        $symptomsLower = mb_strtolower($symptoms, 'UTF-8');
        $suggested = [];

        $keywords = [
            'أمراض القلب والشرايين' => ['قلب', 'صدر', 'ضغط', 'خفقان', 'ضيق تنفس'],
            'الأمراض العصبية' => ['صداع', 'دوخة', 'شلل', 'تنميل', 'تشنج', 'مخ'],
            'أمراض الصدر' => ['تنفس', 'كحة', 'سعال', 'كتمة', 'ربو'],
            'الباطنة العامة' => ['تعب', 'عام', 'فحص'],
        ];

        foreach ($keywords as $dept => $words) {
            foreach ($words as $word) {
                if (stripos($symptomsLower, $word) !== false) {
                    $suggested[] = $dept;
                }
            }
        }

        return !empty($suggested) ? array_unique($suggested) : ['الباطنة العامة'];
    }

    /**
     * جلب الأطباء بناءً على الأقسام
     */
    private function getDoctorsByDepartments(array $departments): array
    {
        if (empty($departments)) return [];

        // الحصول على IDs الأقسام
        $departmentIds = Department::whereIn('name', $departments)->pluck('id')->toArray();

        return Doctor::whereIn('department_id', $departmentIds)
            ->select('id', 'name', 'specialization', 'department_id', 'price', 'rate', 'phone', 'image as image_url', 'degree')
            ->with(['department:id,name'])
            ->orderBy('rate', 'DESC')
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialization' => $doctor->specialization,
                    'department' => $doctor->department->name ?? null,
                    'degree' => $doctor->degree,
                    'rate' => $doctor->rate,
                    'price' => $doctor->price,
                    'phone' => $doctor->phone,
                    'image_url' => $doctor->image_url,
                ];
            })
            ->toArray();
    }
}
