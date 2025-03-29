<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableDoctor extends Model
{
    protected $fillable = ['doctor_id', 'day', 'start_time', 'end_time','type', 'is_booked'];

   
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
