<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\PatientMedication;
use App\Models\PatientHabit;
use App\Models\PatientDisease;
use App\Models\PatientAttachment;
use App\Models\Visit;
use App\Models\Doctor;
use Mpdf\Mpdf;

class PatientController extends Controller
{
    /**
     * Register patient medications
     * POST /api/patient/medications
     */
    public function storeMedication(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $request->validate([
            'medication_name' => 'required|string|max:255',
            'dose' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'patient_notes' => 'nullable|string',
        ]);

        try {
            $medication = PatientMedication::create([
                'patient_id' => $user->id,
                'medication_name' => $request->medication_name,
                'dose' => $request->dose,
                'frequency' => $request->frequency,
                'duration' => $request->duration,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'patient_notes' => $request->patient_notes,
                'is_active' => true,
                'source' => 'patient',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Medication registered successfully',
                'medication' => $medication
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient medications
     * GET /api/patient/medications
     */
    public function getMedications()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $medications = PatientMedication::where('patient_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'medications' => $medications
        ]);
    }

    /**
     * Submit patient survey/form data
     * POST /api/patient/survey
     */
    public function submitSurvey(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $request->validate([
            'habits' => 'nullable|array',
            'habits.smoking' => 'nullable|string|max:255',
            'habits.caffeine' => 'nullable|string|max:255',
            'habits.exercise' => 'nullable|string|max:255',
            'habits.sleep_hours' => 'nullable|integer|min:0|max:24',
            'habits.notes' => 'nullable|string',
            'diseases' => 'nullable|array',
            'diseases.*.disease_name' => 'required_with:diseases|string|max:255',
            'diseases.*.status' => 'nullable|string|in:active,chronic,resolved',
            'medications' => 'nullable|array',
            'medications.*.medication_name' => 'required_with:medications|string|max:255',
            'medications.*.dose' => 'nullable|string|max:255',
            'medications.*.frequency' => 'nullable|string|max:255',
            'medications.*.duration' => 'nullable|string|max:255',
            'medications.*.start_date' => 'nullable|date',
            'medications.*.end_date' => 'nullable|date|after_or_equal:medications.*.start_date',
            'medications.*.patient_notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*.type' => 'required_with:attachments|string|max:255',
            'attachments.*.file' => 'required_with:attachments|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'attachments.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Handle habits
            if ($request->has('habits')) {
                PatientHabit::updateOrCreate(
                    ['patient_id' => $user->id],
                    [
                        'smoking' => $request->habits['smoking'] ?? null,
                        'caffeine' => $request->habits['caffeine'] ?? null,
                        'exercise' => $request->habits['exercise'] ?? null,
                        'sleep_hours' => $request->habits['sleep_hours'] ?? null,
                        'notes' => $request->habits['notes'] ?? null,
                    ]
                );
            }

            // Handle diseases
            if ($request->has('diseases') && is_array($request->diseases)) {
                foreach ($request->diseases as $diseaseData) {
                    PatientDisease::create([
                        'patient_id' => $user->id,
                        'disease_name' => $diseaseData['disease_name'],
                        'status' => $diseaseData['status'] ?? 'active',
                        'source' => 'patient',
                    ]);
                }
            }

            // Handle medications
            if ($request->has('medications') && is_array($request->medications)) {
                foreach ($request->medications as $medicationData) {
                    PatientMedication::create([
                        'patient_id' => $user->id,
                        'medication_name' => $medicationData['medication_name'],
                        'dose' => $medicationData['dose'] ?? null,
                        'frequency' => $medicationData['frequency'] ?? null,
                        'duration' => $medicationData['duration'] ?? null,
                        'start_date' => $medicationData['start_date'] ?? null,
                        'end_date' => $medicationData['end_date'] ?? null,
                        'patient_notes' => $medicationData['patient_notes'] ?? null,
                        'is_active' => true,
                        'source' => 'patient',
                    ]);
                }
            }

            // Handle attachments
            if ($request->has('attachments') && is_array($request->attachments)) {
                foreach ($request->attachments as $index => $attachmentData) {
                    $fileKey = "attachments.{$index}.file";
                    if ($request->hasFile($fileKey)) {
                        $file = $request->file($fileKey);
                        $filePath = $file->store('patient_attachments', 'public');

                        PatientAttachment::create([
                            'patient_id' => $user->id,
                            'type' => $attachmentData['type'] ?? 'document',
                            'file_path' => $filePath,
                            'description' => $attachmentData['description'] ?? null,
                            'source' => 'patient',
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Survey submitted successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient survey data
     * GET /api/patient/survey
     */
    public function getSurvey()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $habits = $user->habits;
        $diseases = $user->diseases;
        $medications = PatientMedication::where('patient_id', $user->id)
            ->where('source', 'patient')
            ->orderBy('created_at', 'desc')
            ->get();
        $attachments = $user->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'type' => $attachment->type,
                'file_url' => asset('storage/' . $attachment->file_path),
                'description' => $attachment->description,
                'created_at' => $attachment->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'habits' => $habits,
                'diseases' => $diseases,
                'medications' => $medications,
                'attachments' => $attachments,
            ]
        ]);
    }

    /**
     * Download visit report for a single visit
     * GET /api/patient/visits/{visitId}/report
     */
    public function downloadSingleVisitReport($visitId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        // Get the specific visit
        $visit = Visit::where('patient_id', $user->id)
            ->where('id', $visitId)
            ->with(['medications', 'doctor', 'patient'])
            ->first();

        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Visit not found or you do not have access to this visit'
            ], 404);
        }

        // Check if visit has doctor_id
        if (!$visit->doctor_id) {
            return response()->json([
                'success' => false,
                'message' => 'This visit does not have an associated doctor'
            ], 404);
        }

        // Load doctor separately to check if it exists
        $doctor = Doctor::find($visit->doctor_id);
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found for this visit',
                'debug' => [
                    'visit_id' => $visit->id,
                    'doctor_id' => $visit->doctor_id,
                    'patient_id' => $visit->patient_id
                ]
            ], 404);
        }

        try {
            // Clean filename from special characters
            $doctorName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $doctor->name);
            $userName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $user->name);
            $visitDate = $visit->visit_date ? $visit->visit_date->format('Y-m-d') : now()->format('Y-m-d');
            $fileName = "Visit_Report_{$visitId}_{$doctorName}_{$userName}_{$visitDate}.pdf";
            $filePath = "pdf_reports/visit_reports/{$fileName}";

            // Ensure directory exists
            if (!Storage::disk('public')->exists('pdf_reports/visit_reports')) {
                Storage::disk('public')->makeDirectory('pdf_reports/visit_reports');
            }

            // Check if file already exists
            if (Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Visit report generated successfully',
                    'pdf_url' => asset("storage/{$filePath}"),
                    'download_url' => asset("storage/{$filePath}")
                ]);
            }

            // Generate PDF
            $mpdf = new Mpdf([
                'default_font' => 'dejavusans',
                'mode' => 'utf-8',
                'format' => 'A4',
            ]);

            $html = view('pdf.visit_report', [
                'visit' => $visit,
                'patient' => $user,
                'doctor' => $doctor,
            ])->render();

            $mpdf->WriteHTML($html);

            // Save PDF to storage
            $pdfContent = $mpdf->Output('', 'S');
            Storage::disk('public')->put($filePath, $pdfContent);

            return response()->json([
                'success' => true,
                'message' => 'Visit report generated successfully',
                'pdf_url' => asset("storage/{$filePath}"),
                'download_url' => asset("storage/{$filePath}"),
                'file_name' => $fileName,
                'visit_id' => $visit->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all visits for the authenticated patient
     * GET /api/patient/visits
     */
    public function getVisits()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $visits = Visit::where('patient_id', $user->id)
            ->with(['doctor'])
            ->orderBy('visit_date', 'desc')
            ->get()
            ->map(function ($visit) {
                return [
                    'id' => $visit->id,
                    'visit_date' => $visit->visit_date ? $visit->visit_date->format('Y-m-d H:i') : null,
                    'doctor' => [
                        'id' => $visit->doctor->id ?? null,
                        'name' => $visit->doctor->name ?? 'N/A',
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'visits' => $visits
        ]);
    }

    /**
     * Get all patient medical data in organized format
     * GET /api/patient/medical-data
     */
    public function getAllMedicalData()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        // Get all diseases
        $diseases = PatientDisease::where('patient_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all medications
        $medications = PatientMedication::where('patient_id', $user->id)
            ->with(['doctor', 'visit'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($medication) {
                return [
                    'id' => $medication->id,
                    'medication_name' => $medication->medication_name,
                    'dose' => $medication->dose,
                    'frequency' => $medication->frequency,
                    'duration' => $medication->duration,
                    'is_active' => $medication->is_active,
                    'source' => $medication->source,
                    'start_date' => $medication->start_date,
                    'end_date' => $medication->end_date,
                    'doctor_notes' => $medication->doctor_notes,
                    'patient_notes' => $medication->patient_notes,
                    'doctor' => $medication->doctor ? [
                        'id' => $medication->doctor->id,
                        'name' => $medication->doctor->name,
                    ] : null,
                    'visit_id' => $medication->visit_id,
                    'created_at' => $medication->created_at,
                    'updated_at' => $medication->updated_at,
                ];
            });

        // Get habits
        $habits = $user->habits;

        // Get attachments
        $attachments = $user->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'type' => $attachment->type,
                'file_url' => asset('storage/' . $attachment->file_path),
                'description' => $attachment->description,
                'source' => $attachment->source,
                'created_at' => $attachment->created_at,
                'updated_at' => $attachment->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'diseases' => $diseases,
                'medications' => $medications,
                'habits' => $habits,
                'attachments' => $attachments,
            ]
        ]);
    }

    /**
     * Mark disease as resolved
     * PUT /api/patient/diseases/{id}/resolve
     */
    public function resolveDisease($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $disease = PatientDisease::where('patient_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$disease) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found or you do not have access to this disease'
            ], 404);
        }

        try {
            $disease->update([
                'status' => 'resolved'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Disease marked as resolved successfully',
                'disease' => $disease
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop medication (set is_active to false)
     * PUT /api/patient/medications/{id}/stop
     */
    public function stopMedication($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $medication = PatientMedication::where('patient_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$medication) {
            return response()->json([
                'success' => false,
                'message' => 'Medication not found or you do not have access to this medication'
            ], 404);
        }

        try {
            $medication->update([
                'is_active' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Medication stopped successfully',
                'medication' => $medication
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

