<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Department;
use App\Services\SymptomAnalyzerService;

class AIAssistantController extends Controller
{
    protected $symptomAnalyzer;

    public function __construct(SymptomAnalyzerService $symptomAnalyzer)
    {
        $this->symptomAnalyzer = $symptomAnalyzer;
    }

    /**
     * تحليل الأعراض واقتراح الأطباء المناسبين
     */
    public function suggestDoctors(Request $request)
    {
        try {
            $request->validate([
                'symptoms' => 'required|string|min:3',
            ]);

            $symptoms = $request->input('symptoms');

            // تحليل الأعراض والحصول على الأقسام المناسبة
            $result = $this->symptomAnalyzer->analyzeSymptoms($symptoms);

            // إذا فشل التحليل
            if (!$result['success'] || empty($result['suggested_departments'])) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'لم نتمكن من تحديد القسم المناسب. يرجى زيارة الطوارئ أو استشارة طبيب عام.',
                    'suggested_doctors' => [],
                    'suggested_departments' => []
                ], 200);
            }

            $suggestedDepartments = $result['suggested_departments'];

            // جلب الأطباء من الأقسام المقترحة مع أقرب موعد متاح
            $doctors = Doctor::whereHas('department', function ($query) use ($suggestedDepartments) {
                $query->whereIn('name', $suggestedDepartments);
            })
            ->where('status', true)
            ->with(['department', 'reviews', 'availableAppointments' => function ($q) {
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
                    'department' => $doctor->department->name,
                    'department_id' => $doctor->department->id,
                    'price' => number_format($doctor->price, 2),
                    'degree' => $doctor->degree,
                    'rate' => $doctor->averageRate(),
                    'image_url' => $doctor->image ? url('storage/' . $doctor->image) : null,
                    'description' => $doctor->description,
                    'phone' => $doctor->phone,
                    'next_available_slot' => $nextSlot ? [
                        'day_of_week' => $nextSlot->day_of_week,
                        'start_time' => $nextSlot->start_time,
                        'end_time' => $nextSlot->end_time,
                        'type' => $nextSlot->type,
                    ] : null,
                ];
            })
            ->filter(fn ($doctor) => $doctor['next_available_slot'] !== null)
            ->sortByDesc('rate')
            ->values();

            // جلب معلومات الأقسام المقترحة
            $departments = Department::whereIn('name', $suggestedDepartments)
                ->where('status', true)
                ->get()
                ->map(function ($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'description' => $dept->description,
                        'image_url' => $dept->image ? url('storage/' . $dept->image) : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'تم تحليل الأعراض بنجاح',
                'symptoms' => $symptoms,
                'suggested_departments' => $departments->pluck('name')->toArray(),
                'suggested_doctors' => $doctors,
                'total_doctors' => $doctors->count(),
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحليل الأعراض',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على قائمة الأعراض الشائعة
     */
    public function getCommonSymptoms()
    {
        try {
            $symptoms = $this->symptomAnalyzer->getCommonSymptoms();

            return response()->json([
                'success' => true,
                'common_symptoms' => $symptoms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأعراض',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

