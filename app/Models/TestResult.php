<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'patient_name',
        'patient_email',
        'doctor_id',
        'doctor_name',
        'doctor_email',
        'note',
        'age',
        'test_date',
        'result_date',
        'tests',
        'total_cost',
        'amount_paid',
        'status',
        'test_status',
        
    ];

    protected $casts = [
        'tests' => 'array',
        'ranges' => 'array',
    ];


    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

 
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($testResult) {
            if (!$testResult->patient_id) {
                $testResult->patient_id = User::where('usertype', 'customer')->first()?->id;
            }
        });
    }
}
