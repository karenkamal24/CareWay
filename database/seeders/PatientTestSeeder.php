<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\PatientDisease;
use App\Models\PatientMedication;
use App\Models\PatientHabit;
use App\Models\PatientAttachment;
use App\Models\Visit;
use App\Models\Appointment;
use App\Models\AvailableDoctor;
use Illuminate\Support\Facades\Hash;

class PatientTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء أو جلب قسم طبي
        $department = Department::firstOrCreate(
            ['name' => 'General Medicine'],
            ['description' => 'General Medicine Department', 'status' => true]
        );

        $doctorUser = User::firstOrCreate(
            ['email' => 'doctor@test.com'],
            [
                'name' => 'Dr. Ahmed Ali',
                'password' => Hash::make('password123'),
                'user_type' => 'admin',
                'phone' => '01234567890',
            ]
        );


        $doctor = Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'department_id' => $department->id,
                'name' => 'Dr. Ahmed Ali',
                'phone' => '01234567890',
                'specialization' => 'General Practitioner',
                'price' => 200,
                'status' => true,
                'degree' => 'MD',
            ]
        );


        $patient = User::firstOrCreate(
            ['email' => 'patient@test.com'],
            [
                'name' => 'Mohamed Hassan',
                'password' => Hash::make('password123'),
                'user_type' => 'user',
                'phone' => '01111111111',
                'gender' => 'male',
                'date_of_birth' => '1990-05-15',
                'address' => 'Cairo, Egypt',
            ]
        );

        // إنشاء أمراض للمريض
        PatientDisease::create([
            'patient_id' => $patient->id,
            'disease_name' => 'Diabetes Type 2',
            'status' => 'active',
            'source' => 'doctor',
        ]);

        PatientDisease::create([
            'patient_id' => $patient->id,
            'disease_name' => 'Hypertension',
            'status' => 'active',
            'source' => 'doctor',
        ]);


        PatientDisease::create([
            'patient_id' => $patient->id,
            'disease_name' => 'Seasonal Allergy',
            'status' => 'resolved',
            'source' => 'patient',
        ]);

        // إنشاء أدوية للمريض
        PatientMedication::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'medication_name' => 'Metformin 500mg',
            'dose' => '500mg',
            'frequency' => 'Twice daily',
            'duration' => '30 days',
            'is_active' => true,
            'source' => 'doctor',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
            'doctor_notes' => 'Take with meals',
            'patient_notes' => null,
        ]);

        PatientMedication::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'medication_name' => 'Lisinopril 10mg',
            'dose' => '10mg',
            'frequency' => 'Once daily',
            'duration' => '30 days',
            'is_active' => true,
            'source' => 'doctor',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
            'doctor_notes' => 'Monitor blood pressure',
            'patient_notes' => null,
        ]);

        PatientMedication::create([
            'patient_id' => $patient->id,
            'doctor_id' => null,
            'medication_name' => 'Vitamin D3',
            'dose' => '1000 IU',
            'frequency' => 'Once daily',
            'duration' => 'Ongoing',
            'is_active' => true,
            'source' => 'patient',
            'start_date' => now()->subDays(30),
            'end_date' => null,
            'doctor_notes' => null,
            'patient_notes' => 'Taking for general health',
        ]);


        PatientHabit::create([
            'patient_id' => $patient->id,
            'smoking' => 'non-smoker',
            'caffeine' => '2-3 cups per day',
            'exercise' => '3 times per week',
            'sleep_hours' => 7,
            'notes' => 'Regular exercise routine',
        ]);


        PatientAttachment::create([
            'patient_id' => $patient->id,
            'type' => 'prescription', // ✔ موجود في ENUM
            'file_path' => 'attachments/prescription_001.pdf',
            'description' => 'Prescription from last visit',
            'source' => 'doctor',
        ]);

        PatientAttachment::create([
            'patient_id' => $patient->id,
            'type' => 'lab',
            'file_path' => 'attachments/lab_result_001.pdf',
            'description' => 'Blood test results',
            'source' => 'patient',
        ]);

        PatientAttachment::create([
            'patient_id' => $patient->id,
            'type' => 'radiology',
            'file_path' => 'attachments/xray_001.jpg',
            'description' => 'Chest X-ray',
            'source' => 'doctor',
        ]);



        Visit::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'symptoms' => 'Headache, fatigue, increased thirst',
            'diagnosis' => 'Diabetes Type 2 - Follow up visit',
            'notes' => 'Patient responding well to medication. Blood sugar levels improving.',
        ]);

        Visit::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'symptoms' => 'High blood pressure readings at home',
            'diagnosis' => 'Hypertension - Initial diagnosis',
            'notes' => 'Started on Lisinopril. Monitor BP daily.',
        ]);

        Visit::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'symptoms' => 'Routine checkup',
            'diagnosis' => 'General health check',
            'notes' => 'All vitals normal. Continue current medications.',
        ]);

$slot = AvailableDoctor::create([
    'doctor_id' => $doctor->id,
    'day_of_week' => 'Monday',
    'start_time' => '09:00:00',
    'end_time' => '12:00:00',
    'type' => 'clinic',
]);

// إنشاء Appointment للمريض
Appointment::create([
    'user_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'available_doctor_id' => $slot->id,
    'type' => 'clinic',
    'appointment_time' => now()->addDays(1)->setTime(10, 0),
    'payment_status' => 'completed',
    'amount' => 200,
    'payment_method' => 'cash',
    'paymob_order_id' => null,
    'status' => 'scheduled',
]);

        $this->command->info('✅ Test patient data created successfully!');
        $this->command->info('Patient: ' . $patient->name . ' (' . $patient->email . ')');
        $this->command->info('Doctor: ' . $doctor->name);
    }
}
