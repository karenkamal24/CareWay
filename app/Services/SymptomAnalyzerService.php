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

            // استخدام fallback أولاً (أكثر دقة للأعراض الواضحة) - قسم واحد فقط
            $suggestedDepartment = $this->fallbackKeywordMatching($symptoms, $departments);

            // Log للتحقق
            if (!empty($suggestedDepartment)) {
                Log::info('Fallback matched department', [
                    'symptoms' => $symptoms,
                    'department' => $suggestedDepartment
                ]);
            }

            // إذا fallback لم يعطي نتيجة، نستخدم AI
            if (empty($suggestedDepartment)) {
                $suggestedDepartment = $this->parseAIResponse($aiResponse, $departments);
                if (!empty($suggestedDepartment)) {
                    Log::info('AI matched department', [
                        'symptoms' => $symptoms,
                        'department' => $suggestedDepartment,
                        'ai_response' => $aiResponse
                    ]);
                }
            }

            // إذا لم نجد أي قسم، نستخدم الباطنة العامة كحل افتراضي
            if (empty($suggestedDepartment)) {
                $suggestedDepartment = 'الباطنة العامة';
                Log::info('Using default department', ['symptoms' => $symptoms]);
            }

            // جلب الأطباء حسب القسم مع أقرب موعد متاح
            $suggestedDoctors = $this->getDoctorsByDepartments([$suggestedDepartment]);

            return [
                'success' => true,
                'message' => 'تم تحليل الأعراض بنجاح',
                'symptoms' => $symptoms,
                'suggested_departments' => [$suggestedDepartment],
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

قم بتحليل الأعراض واقترح اسم القسم الطبي المناسب الوحيد فقط (قسم واحد فقط).

قواعد مهمة للتصنيف:
- كحة، سعال، رشح، زكام، نزلة برد، ضيق تنفس، كتمة، ربو → أمراض الصدر
- ألم في الرجل، قدم، يد، ذراع، ركبة، كتف، ظهر، عظام، مفاصل، كسر → جراحة العظام
- ألم في الصدر، قلب، ضغط، خفقان → أمراض القلب والشرايين
- صداع، دوخة، تنميل، شلل → الأمراض العصبية
- ألم في البطن، غثيان، قيء، إسهال → أمراض الجهاز الهضمي أو الباطنة العامة

أجب باسم القسم الوحيد فقط بدون شرح أو فواصل.";
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
     * استخراج القسم من رد الذكاء الاصطناعي (قسم واحد فقط)
     */
    private function parseAIResponse(string $aiResponse, array $availableDepartments): string
    {
        if (empty($aiResponse)) return '';

        // البحث عن أول قسم يطابق في رد AI
        foreach ($availableDepartments as $dept) {
            if (stripos($aiResponse, $dept) !== false) {
                return $dept;
            }
        }
        return '';
    }

    /**
     * Fallback في حالة عدم توفر AI - يرجع قسم واحد فقط (الأكثر ملاءمة)
     */
    private function fallbackKeywordMatching(string $symptoms, array $departments): string
    {
        // تنظيف النص وإزالة المسافات الزائدة
        $symptomsClean = trim($symptoms);
        $symptomsLower = mb_strtolower($symptomsClean, 'UTF-8');

        // ترتيب الأقسام حسب الأولوية (الأكثر تحديداً أولاً)
        // يجب أن تكون الأقسام المحددة جداً في البداية
        $keywords = [
            'الأمراض العصبية' => ['صداع', 'دوخة', 'شلل', 'تنميل', 'مخ', 'نوبة', 'صرع', 'رعشة'],
            'أمراض الصدر' => ['كحة', 'كحه', 'كح', 'سعال', 'رشح', 'زكام', 'نزلة برد', 'نزلة', 'برد', 'أنف', 'احتقان', 'عطس', 'بلغم', 'صوت أجش', 'تنفس', 'ربو', 'كتمة'],
            'جراحة العظام' => ['رجل', 'قدم', 'عظام', 'مفصل', 'كسر', 'كاحل', 'ركبة', 'فخذ', 'يد', 'ذراع', 'ظهر', 'عمود فقري', 'الم في العظام', 'الم في المفاصل', 'الم في الرجل', 'الم في القدم', 'الم في الركبة', 'الم في الكتف', 'الم في الظهر', 'الم في اليد', 'الم في الذراع', 'الم في الكاحل', 'الم في الفخذ', 'الم في العضلات', 'التواء', 'خلع', 'كسور', 'مفاصل'],
            'أمراض القلب والشرايين' => ['قلب', 'صدر', 'ضغط', 'خفقان', 'ضيق تنفس'],
            'الباطنة العامة' => ['فحص', 'تعب', 'عام'],
        ];

        // البحث عن أول قسم يطابق (الأكثر تحديداً)
        foreach ($keywords as $dept => $words) {
            foreach ($words as $word) {
                $wordLower = mb_strtolower(trim($word), 'UTF-8');
                // البحث في النص - استخدام mb_stripos للعربية
                if (mb_stripos($symptomsLower, $wordLower, 0, 'UTF-8') !== false) {
                    Log::info('Fallback keyword matched', [
                        'symptoms' => $symptoms,
                        'symptoms_lower' => $symptomsLower,
                        'keyword' => $word,
                        'keyword_lower' => $wordLower,
                        'department' => $dept
                    ]);
                    return $dept; // إرجاع أول قسم يطابق
                }
            }
        }

        Log::info('Fallback no match found', ['symptoms' => $symptoms]);
        return ''; // لا قسم مطابق
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

    /**
     * الحصول على قائمة الأعراض الشائعة
     */
    public function getCommonSymptoms(): array
    {
        return [
            'ألم في الرجل',
            'ألم في القدم',
            'ألم في الركبة',
            'ألم في الظهر',
            'ألم في الكتف',
            'ألم في اليد',
            'ألم في الذراع',
            'صداع',
            'دوخة',
            'ألم في الصدر',
            'ضيق تنفس',
            'سعال',
            'كحة',
            'ألم في البطن',
            'غثيان',
            'تعب وإرهاق',
            'حمى',
            'ألم في الحلق',
            'ألم في الأذن',
            'مشاكل في الرؤية',
        ];
    }
}
