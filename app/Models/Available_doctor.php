<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Available_doctor extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'start_time',  'end_time', 'day'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
