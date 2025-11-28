<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Visit extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_date',
        'symptoms',
        'diagnosis',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function medications()
{
    return $this->hasMany(\App\Models\PatientMedication::class, 'visit_id', 'id');
}
public function habits()
{
    return $this->belongsTo(\App\Models\PatientHabit::class, 'patient_id', 'patient_id');
}

}

