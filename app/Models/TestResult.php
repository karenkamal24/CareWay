<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'test_name', 'result', 'unit', 'range', 'test_date', 'result_date', 'doctor_id',  'note'
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
