<?php

namespace App\Services;

use App\Models\PatientMedication;
use App\Models\PatientDisease;
use App\Models\PatientHabit;
use App\Models\Visit;
use App\Models\Doctor;

class MedicalTextParserService
{
    /**
     * Parse medical text and extract medications, diseases, symptoms, and notes
     *
     * @param string $text
     * @param int $patientId
     * @param int $doctorId
     * @return array
     */
    public function parseAndSave(string $text, int $patientId, int $doctorId): array
    {
        $results = [
            'medications' => [],
            'diseases' => [],
            'habits' => null,
            'visit' => null,
        ];

        // Split text into lines
        $lines = array_filter(array_map('trim', explode("\n", $text)));

        $medications = [];
        $diseases = [];
        $symptoms = [];
        $diagnosis = '';
        $notes = [];
        $habits = [];

        $currentSection = 'general';

        foreach ($lines as $line) {
            $lineLower = strtolower($line);

            // Detect sections
            if (preg_match('/^(medications?|drugs?|أدوية?)/i', $line)) {
                $currentSection = 'medications';
                continue;
            }

            if (preg_match('/^(diseases?|diagnosis|تشخيص|أمراض?)/i', $line)) {
                $currentSection = 'diseases';
                continue;
            }

            if (preg_match('/^(symptoms?|أعراض?)/i', $line)) {
                $currentSection = 'symptoms';
                continue;
            }

            if (preg_match('/^(notes?|ملاحظات?)/i', $line)) {
                $currentSection = 'notes';
                continue;
            }

            if (preg_match('/^(habits?|عادات?)/i', $line)) {
                $currentSection = 'habits';
                continue;
            }

            // Parse based on section
            switch ($currentSection) {
                case 'medications':
                    $medication = $this->parseMedication($line);
                    if ($medication) {
                        $medications[] = $medication;
                    }
                    break;

                case 'diseases':
                    $disease = $this->parseDisease($line);
                    if ($disease) {
                        $diseases[] = $disease;
                    }
                    break;

                case 'symptoms':
                    $symptoms[] = $line;
                    break;

                case 'notes':
                    $notes[] = $line;
                    break;

                case 'habits':
                    $habits[] = $line;
                    break;

                default:
                    // Try to auto-detect if it's a medication or disease
                    $medication = $this->parseMedication($line);
                    if ($medication) {
                        $medications[] = $medication;
                    } else {
                        $disease = $this->parseDisease($line);
                        if ($disease) {
                            $diseases[] = $disease;
                        } else {
                            $notes[] = $line;
                        }
                    }
            }
        }

        // Save medications
        foreach ($medications as $med) {
            $medication = PatientMedication::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'medication_name' => $med['name'],
                'dose' => $med['dose'] ?? null,
                'frequency' => $med['frequency'] ?? null,
                'duration' => $med['duration'] ?? null,
                'is_active' => true,
                'source' => 'doctor',
                'start_date' => now(),
                'doctor_notes' => $med['notes'] ?? null,
            ]);
            $results['medications'][] = $medication;
        }

        // Save diseases
        foreach ($diseases as $dis) {
            $disease = PatientDisease::create([
                'patient_id' => $patientId,
                'disease_name' => $dis['name'],
                'status' => $dis['status'] ?? 'active',
                'source' => 'doctor',
            ]);
            $results['diseases'][] = $disease;
        }

        // Create visit record FIRST (with current date) - Always create a visit
        $visit = Visit::create([
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'visit_date' => now(),
            'symptoms' => !empty($symptoms) ? implode(', ', $symptoms) : null,
            'diagnosis' => $diagnosis ?: (!empty($diseases) ? implode(', ', array_column($diseases, 'name')) : 'General consultation'),
            'notes' => !empty($notes) ? implode("\n", $notes) : (!empty($medications) || !empty($diseases) ? 'Visit data recorded' : null),
        ]);
        $results['visit'] = $visit;

        // Save medications (linked to visit date)
        foreach ($medications as $med) {
            $medication = PatientMedication::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'medication_name' => $med['name'],
                'dose' => $med['dose'] ?? null,
                'frequency' => $med['frequency'] ?? null,
                'duration' => $med['duration'] ?? null,
                'is_active' => true,
                'source' => 'doctor',
                'start_date' => $visit->visit_date,
                'doctor_notes' => $med['notes'] ?? null,
            ]);
            $results['medications'][] = $medication;
        }

        // Save diseases (linked to visit date)
        foreach ($diseases as $dis) {
            $disease = PatientDisease::create([
                'patient_id' => $patientId,
                'disease_name' => $dis['name'],
                'status' => $dis['status'] ?? 'active',
                'source' => 'doctor',
            ]);
            $results['diseases'][] = $disease;
        }

        // Save habits
        if (!empty($habits)) {
            $habitData = $this->parseHabits($habits);
            $habit = PatientHabit::updateOrCreate(
                ['patient_id' => $patientId],
                $habitData
            );
            $results['habits'] = $habit;
        }

        return $results;
    }

    /**
     * Parse medication line
     * Examples:
     * - "Metformin 500mg twice daily"
     * - "Lisinopril 10mg once daily for 30 days"
     * - "Aspirin 100mg"
     */
    protected function parseMedication(string $line): ?array
    {
        // Skip if line is too short or doesn't look like medication
        if (strlen($line) < 3) {
            return null;
        }

        // Pattern: medication name, dose, frequency, duration
        $pattern = '/([A-Za-z\s]+?)\s*(\d+\s*(?:mg|ml|g|mcg|iu|unit)?s?)?\s*(?:-|,|:)?\s*(?:take|twice|once|daily|every|for)?\s*([^,]+)?/i';

        if (preg_match($pattern, $line, $matches)) {
            $name = trim($matches[1] ?? '');
            $dose = trim($matches[2] ?? '');
            $frequency = trim($matches[3] ?? '');

            // Extract duration if exists
            $duration = null;
            if (preg_match('/for\s+(\d+)\s*(?:days?|weeks?|months?)/i', $line, $durMatch)) {
                $duration = $durMatch[1] . ' ' . ($durMatch[2] ?? 'days');
            }

            // Extract frequency keywords
            if (preg_match('/(twice|once|three times|daily|every)/i', $line, $freqMatch)) {
                $frequency = $freqMatch[1];
            }

            return [
                'name' => $name ?: $line,
                'dose' => $dose ?: null,
                'frequency' => $frequency ?: null,
                'duration' => $duration,
                'notes' => null,
            ];
        }

        // If no pattern match, treat whole line as medication name
        return [
            'name' => trim($line),
            'dose' => null,
            'frequency' => null,
            'duration' => null,
            'notes' => null,
        ];
    }

    /**
     * Parse disease line
     */
    protected function parseDisease(string $line): ?array
    {
        if (strlen($line) < 3) {
            return null;
        }

        $status = 'active';
        if (preg_match('/(resolved|healed|cured|inactive|past)/i', $line)) {
            $status = 'resolved';
        }

        // Remove status keywords
        $name = preg_replace('/(resolved|healed|cured|inactive|past)/i', '', $line);
        $name = trim($name);

        if (empty($name)) {
            return null;
        }

        return [
            'name' => $name,
            'status' => $status,
        ];
    }

    /**
     * Parse habits from text
     */
    protected function parseHabits(array $lines): array
    {
        $habits = [
            'smoking' => null,
            'caffeine' => null,
            'exercise' => null,
            'sleep_hours' => null,
            'notes' => null,
        ];

        foreach ($lines as $line) {
            $lineLower = strtolower($line);

            if (preg_match('/smok(?:ing|er)?[:\s]+(.+)/i', $line, $matches)) {
                $habits['smoking'] = trim($matches[1]);
            } elseif (preg_match('/caffeine[:\s]+(.+)/i', $line, $matches)) {
                $habits['caffeine'] = trim($matches[1]);
            } elseif (preg_match('/exerc(?:ise|ing)?[:\s]+(.+)/i', $line, $matches)) {
                $habits['exercise'] = trim($matches[1]);
            } elseif (preg_match('/sleep[:\s]+(\d+)/i', $line, $matches)) {
                $habits['sleep_hours'] = (int)$matches[1];
            } else {
                $habits['notes'] = ($habits['notes'] ? $habits['notes'] . "\n" : '') . $line;
            }
        }

        return $habits;
    }
}


