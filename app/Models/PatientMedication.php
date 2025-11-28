<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientMedication extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'medication_name',
        'dose',
        'frequency',
        'duration',
        'is_active',
        'source',
        'visit_id',
        'start_date',
        'end_date',
        'doctor_notes',
        'patient_notes',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function visit()
{
    return $this->belongsTo(Visit::class);
}

}
