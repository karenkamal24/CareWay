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
            $suggestedDepartments = $this->symptomAnalyzer->analyzeSymptoms($symptoms);

            if (empty($suggestedDepartments)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم نتمكن من تحديد القسم المناسب. يرجى زيارة الطوارئ أو استشارة طبيب عام.',
                    'suggested_doctors' => [],
                    'suggested_departments' => []
                ], 200);
            }

            // جلب الأطباء من الأقسام المقترحة
            $doctors = Doctor::whereHas('department', function ($query) use ($suggestedDepartments) {
                $query->whereIn('name', $suggestedDepartments);
            })
            ->where('status', true)
            ->with(['department', 'reviews'])
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialization' => $doctor->specialization,
                    'department' => $doctor->department->name,
                    'department_id' => $doctor->department->id,
                    'price' => $doctor->price,
                    'degree' => $doctor->degree,
                    'rate' => $doctor->averageRate(),
                    'image_url' => $doctor->image ? url('storage/' . $doctor->image) : null,
                    'description' => $doctor->description,
                    'phone' => $doctor->phone,
                ];
            })
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
                'message' => 'تم العثور على ' . $doctors->count() . ' طبيب مناسب',
                'symptoms' => $symptoms,
                'suggested_departments' => $departments,
                'suggested_doctors' => $doctors,
                'total_doctors' => $doctors->count(),
            ]);

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

