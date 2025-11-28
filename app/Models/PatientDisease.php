<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDisease extends Model
{
    protected $fillable = [
        'patient_id',
        'disease_name',
        'status',
        'source'
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
