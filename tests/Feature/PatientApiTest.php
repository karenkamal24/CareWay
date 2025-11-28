<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Visit;
use App\Models\PatientMedication;
use App\Models\PatientHabit;
use App\Models\PatientDisease;
use App\Models\PatientAttachment;
use App\Models\Appointment;
use App\Models\AvailableDoctor;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PatientApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $doctor;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create a test department
        $department = Department::create([
            'name' => 'Test Department',
            'description' => 'Test Description',
            'status' => 1,
        ]);

        // Create a test doctor
        $this->doctor = Doctor::create([
            'name' => 'Dr. Test',
            'specialization' => 'Cardiology',
            'department_id' => $department->id,
            'price' => 100,
            'status' => 'active',
        ]);

        // Login and get token
        $response = $this->postJson('/api/login', [
            'email' => 'patient@test.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token') ?? $response->json('token');
    }

    /**
     * Test storing patient medication
     */
    public function test_store_medication()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/patient/medications', [
            'medication_name' => 'Aspirin',
            'dose' => '100mg',
            'frequency' => 'Once daily',
            'duration' => '7 days',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'patient_notes' => 'Take with food',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Medication registered successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'medication' => [
                    'id',
                    'medication_name',
                    'dose',
                    'frequency',
                    'duration',
                ],
            ]);
    }

    /**
     * Test getting patient medications
     */
    public function test_get_medications()
    {
        // Create test medications
        PatientMedication::create([
            'patient_id' => $this->user->id,
            'medication_name' => 'Medication 1',
            'dose' => '100mg',
            'is_active' => true,
            'source' => 'patient',
        ]);
        PatientMedication::create([
            'patient_id' => $this->user->id,
            'medication_name' => 'Medication 2',
            'dose' => '50mg',
            'is_active' => true,
            'source' => 'patient',
        ]);
        PatientMedication::create([
            'patient_id' => $this->user->id,
            'medication_name' => 'Medication 3',
            'dose' => '200mg',
            'is_active' => true,
            'source' => 'patient',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/patient/medications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'medications' => [
                    '*' => [
                        'id',
                        'medication_name',
                        'patient_id',
                    ],
                ],
            ]);
    }

    /**
     * Test submitting survey with habits only
     */
    public function test_submit_survey_habits()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/patient/survey', [
            'habits' => [
                'smoking' => 'No',
                'caffeine' => '2 cups per day',
                'exercise' => '3 times per week',
                'sleep_hours' => 8,
                'notes' => 'Regular exercise routine',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Survey submitted successfully',
            ]);
    }

    /**
     * Test submitting survey with diseases
     */
    public function test_submit_survey_diseases()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/patient/survey', [
            'diseases' => [
                [
                    'disease_name' => 'Diabetes',
                    'status' => 'chronic',
                ],
                [
                    'disease_name' => 'Hypertension',
                    'status' => 'active',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Survey submitted successfully',
            ]);
    }

    /**
     * Test submitting survey with medications
     */
    public function test_submit_survey_medications()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/patient/survey', [
            'medications' => [
                [
                    'medication_name' => 'Aspirin',
                    'dose' => '100mg',
                    'frequency' => 'Once daily',
                    'duration' => '7 days',
                    'start_date' => now()->format('Y-m-d'),
                    'end_date' => now()->addDays(7)->format('Y-m-d'),
                    'patient_notes' => 'Take with food',
                ],
                [
                    'medication_name' => 'Paracetamol',
                    'dose' => '500mg',
                    'frequency' => 'Twice daily',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Survey submitted successfully',
            ]);
    }

    /**
     * Test submitting complete survey with all fields
     */
    public function test_submit_complete_survey()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/patient/survey', [
            'habits' => [
                'smoking' => 'No',
                'caffeine' => '2 cups per day',
                'sleep_hours' => 8,
            ],
            'diseases' => [
                [
                    'disease_name' => 'Diabetes',
                    'status' => 'chronic',
                ],
            ],
            'medications' => [
                [
                    'medication_name' => 'Aspirin',
                    'dose' => '100mg',
                    'frequency' => 'Once daily',
                ],
            ],
            'attachments' => [
                [
                    'type' => 'xray',
                    'file' => $file,
                    'description' => 'Chest X-ray',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Survey submitted successfully',
            ]);
    }

    /**
     * Test submitting survey with attachments
     */
    public function test_submit_survey_with_attachments()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/patient/survey', [
            'attachments' => [
                [
                    'type' => 'xray',
                    'file' => $file,
                    'description' => 'Chest X-ray',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Survey submitted successfully',
            ]);
    }

    /**
     * Test getting survey data
     */
    public function test_get_survey()
    {
        // Create test data
        PatientHabit::create([
            'patient_id' => $this->user->id,
            'smoking' => 'No',
            'caffeine' => '2 cups',
            'sleep_hours' => 8,
        ]);

        PatientDisease::create([
            'patient_id' => $this->user->id,
            'disease_name' => 'Diabetes',
            'status' => 'chronic',
            'source' => 'patient',
        ]);

        PatientDisease::create([
            'patient_id' => $this->user->id,
            'disease_name' => 'Hypertension',
            'status' => 'active',
            'source' => 'patient',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/patient/survey');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'habits',
                    'diseases',
                    'medications',
                    'attachments',
                ],
            ]);
    }

    /**
     * Test getting patient visits
     */
    public function test_get_visits()
    {
        // Create test visits
        Visit::create([
            'patient_id' => $this->user->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => now()->subDays(5),
            'symptoms' => 'Chest pain',
            'diagnosis' => 'Angina',
        ]);
        Visit::create([
            'patient_id' => $this->user->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => now()->subDays(3),
            'symptoms' => 'Headache',
            'diagnosis' => 'Migraine',
        ]);
        Visit::create([
            'patient_id' => $this->user->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => now()->subDays(1),
            'symptoms' => 'Fever',
            'diagnosis' => 'Common cold',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/patient/visits');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'visits' => [
                    '*' => [
                        'id',
                        'visit_date',
                        'doctor_name',
                        'symptoms',
                    ],
                ],
            ]);
    }

    /**
     * Test downloading visit report
     */
    public function test_download_visit_report()
    {
        // Create test visit
        Visit::create([
            'patient_id' => $this->user->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => now(),
            'symptoms' => 'Test symptoms',
            'diagnosis' => 'Test diagnosis',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/patient/visits/{$this->doctor->id}/report");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test download visit report with no visits
     */
    public function test_download_visit_report_no_visits()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/patient/visits/{$this->doctor->id}/report");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No visits found for this doctor',
            ]);
    }

    /**
     * Test authentication required
     */
    public function test_medications_requires_authentication()
    {
        $response = $this->postJson('/api/patient/medications', [
            'medication_name' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test appointment booking restriction
     */
    public function test_cannot_book_appointment_if_exists()
    {
        // Create an existing appointment
        $availableDoctor = AvailableDoctor::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'type' => 'clinic',
            'capacity' => 10,
            'booked_count' => 0,
            'is_booked' => false,
        ]);

        Appointment::create([
            'user_id' => $this->user->id,
            'doctor_id' => $this->doctor->id,
            'available_doctor_id' => $availableDoctor->id,
            'type' => 'clinic',
            'appointment_time' => now(),
            'status' => 'scheduled',
            'payment_status' => 'pending',
            'amount' => 100,
            'payment_method' => 'cash',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/appointments', [
            'available_doctor_id' => $availableDoctor->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'You already have an active appointment. Please complete or cancel your current appointment before booking a new one.',
            ]);
    }
}

