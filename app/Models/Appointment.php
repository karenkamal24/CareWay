<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'available_doctor_id',
        'type',
        'appointment_time',
        'payment_status',
        'amount',
        'payment_method',
        'paymob_order_id'

    ];
    protected $casts = [
        'appointment_time' => 'datetime', 
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }


    public function AvailableDoctor()
    {
        return $this->belongsTo(AvailableDoctor::class, 'available_doctor_id');
    }
}
