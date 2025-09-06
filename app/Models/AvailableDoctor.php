<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableDoctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'type',
        'is_booked',
        'is_recurring',
        'capacity',
        'booked_count'

    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
       public function hasAvailableSlots(): bool
    {
        return $this->booked_count < $this->capacity;
    }
        public function getDayOfWeekNameAttribute(): string
{
    $days = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    return $days[$this->day_of_week] ?? 'Unknown';
}
}
