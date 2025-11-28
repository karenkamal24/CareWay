<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'type',
        'file_path',
        'description',
        'source',
    ];

 
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
