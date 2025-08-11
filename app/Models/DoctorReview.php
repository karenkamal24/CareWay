<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorReview extends Model
{
    protected $fillable = ['doctor_id', 'user_id', 'rate', 'comment'];

  
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
