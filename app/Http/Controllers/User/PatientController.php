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
                'attachments' => $attachments,
            ]
        ]);
    }

    /**
     * Download visit report for a specific doctor
     * GET /api/patient/visits/{doctorId}/report
     */
    public function downloadVisitReport($doctorId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        // Get all visits for this patient with this doctor
        $visits = Visit::where('patient_id', $user->id)
            ->where('doctor_id', $doctorId)
            ->with(['medications', 'doctor', 'patient'])
            ->orderBy('visit_date', 'desc')
            ->get();

        if ($visits->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No visits found for this doctor'
            ], 404);
        }

        try {
            $mpdf = new Mpdf([
                'default_font' => 'dejavusans',
                'mode' => 'utf-8',
                'format' => 'A4',
            ]);

            $html = '';

            // Generate PDF for each visit
            foreach ($visits as $visit) {
                $visitHtml = view('pdf.visit_report', [
                    'visit' => $visit,
                    'patient' => $user,
                    'doctor' => $doctor,
                ])->render();

                $html .= $visitHtml;

                // Add page break between visits (except for the last one)
                if ($visit !== $visits->last()) {
                    $html .= '<pagebreak />';
                }
            }

            $mpdf->WriteHTML($html);

            $fileName = "Visit_Report_{$doctor->name}_{$user->name}_" . now()->format('Y-m-d') . ".pdf";

            return response()->streamDownload(
                fn() => print($mpdf->Output('', 'S')),
                $fileName
            );

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
            ->with(['doctor', 'medications'])
            ->orderBy('visit_date', 'desc')
            ->get()
            ->map(function ($visit) {
                return [
                    'id' => $visit->id,
                    'visit_date' => $visit->visit_date->format('Y-m-d H:i'),
                    'doctor_name' => $visit->doctor->name ?? 'N/A',
                    'doctor_specialization' => $visit->doctor->specialization ?? 'N/A',
                    'symptoms' => $visit->symptoms,
                    'diagnosis' => $visit->diagnosis,
                    'notes' => $visit->notes,
                    'medications' => $visit->medications,
                ];
            });

        return response()->json([
            'success' => true,
            'visits' => $visits
        ]);
    }
}

