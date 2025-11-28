<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientHabit extends Model
{
    protected $fillable = [
        'patient_id',
        'smoking',
        'caffeine',
        'exercise',
        'sleep_hours',
        'notes',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
