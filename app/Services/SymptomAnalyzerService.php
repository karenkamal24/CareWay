<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Department;
use App\Models\Doctor;

class SymptomAnalyzerService
{
    /**
     * تحليل الأعراض باستخدام الذكاء الاصطناعي واقتراح الأقسام والأطباء الأقرب موعدًا
     */
    public function analyzeSymptoms(string $symptoms): array
    {
        try {
            // جلب الأقسام من قاعدة البيانات
            $departments = Department::where('status', true)->pluck('name')->toArray();

            // بناء prompt لإرسال الأعراض للذكاء الاصطناعي
            $prompt = $this->buildPrompt($symptoms, $departments);

            // استدعاء Groq API
            $aiResponse = $this->callGroqAPI($prompt);

            // تحليل الرد واستخراج الأقسام المناسبة
            $suggestedDepartments = $this->parseAIResponse($aiResponse, $departments);

            // Fallback في حالة عدم تعرف الذكاء الاصطناعي على القسم
            if (empty($suggestedDepartments)) {
                $suggestedDepartments = $this->fallbackKeywordMatching($symptoms, $departments);
            }

            // جلب الأطباء حسب الأقسام مع أقرب موعد متاح
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
     * بناء Prompt للذكاء الاصطناعي
     */
    private function buildPrompt(string $symptoms, array $departments): string
    {
        $departmentsList = implode(', ', $departments);

        return "أنت مساعد طبي ذكي. المستخدم يعاني من الأعراض التالية: {$symptoms}

الأقسام الطبية المتاحة:
{$departmentsList}

قم بتحليل الأعراض واقترح اسم القسم الطبي المناسب فقط.
أجب بقائمة أقسام مفصولة بفواصل بدون شرح.";
    }

    /**
     * استدعاء Groq API
     */
    private function callGroqAPI(string $prompt): string
    {
        try {
            $apiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');

            if (empty($apiKey)) {
                Log::warning('Groq API Key is missing. Using fallback mode.');
                return '';
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
            return '';
        }
    }

    /**
     * استخراج الأقسام من رد الذكاء الاصطناعي
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
     * Fallback في حالة عدم توفر AI
     */
    private function fallbackKeywordMatching(string $symptoms, array $departments): array
    {
        $symptomsLower = mb_strtolower($symptoms, 'UTF-8');
        $suggested = [];

        $keywords = [
            'أمراض القلب والشرايين' => ['قلب', 'صدر', 'ضغط', 'خفقان', 'ضيق تنفس'],
            'الأمراض العصبية' => ['صداع', 'دوخة', 'شلل', 'تنميل', 'مخ'],
            'أمراض الصدر' => ['تنفس', 'سعال', 'كحة', 'ربو', 'كتمة'],
            'الباطنة العامة' => ['فحص', 'تعب', 'عام'],
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
     * جلب الأطباء بناءً على الأقسام + ترتيبهم حسب أقرب موعد متاح
     */
    private function getDoctorsByDepartments(array $departments): array
    {
        if (empty($departments)) return [];

        $departmentIds = Department::whereIn('name', $departments)->pluck('id')->toArray();

        $doctors = Doctor::whereIn('department_id', $departmentIds)
            ->with(['department:id,name', 'availableAppointments' => function ($q) {
                $q->where('is_booked', false)
                  ->whereColumn('booked_count', '<', 'capacity')
                  ->orderBy('start_time', 'ASC');
            }])
            ->get()
            ->map(function ($doctor) {
                $nextSlot = $doctor->availableAppointments->first();

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
                    'next_available_slot' => $nextSlot ? [
                        'day_of_week' => $nextSlot->day_of_week,
                        'start_time' => $nextSlot->start_time,
                        'end_time' => $nextSlot->end_time,
                        'type' => $nextSlot->type,
                    ] : null,
                ];
            })
            ->filter(fn ($doctor) => $doctor['next_available_slot'] !== null)
            ->sortBy(fn ($doctor) => $doctor['next_available_slot']['start_time'])
            ->values()
            ->toArray();

        return $doctors;
    }
}
