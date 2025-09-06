<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

class Doctor extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['department_id', 'user_id', 'name',  'phone', 'description', 'specialization', 'price', 'image', 'status', 'degree', 'rate'];


    protected $with = ['roles'];


    protected $guard_name = 'doctor';


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

public function availableAppointments()
{
    return $this->hasMany(\App\Models\AvailableDoctor::class, 'doctor_id');
}



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function scopeWithRoles(Builder $query): Builder
    {
        return $query->with('roles');
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->hasRole('doctor');
    }
public function reviews()
{
    return $this->hasMany(DoctorReview::class);
}

public function averageRate()
{
    return $this->reviews()->avg('rate') ?? 0;
}

public function scopeDegree($query, $degree)
{
    if ($degree) {
        return $query->where('degree', $degree);
    }
    return $query;
}
public function testResults()
{
    return $this->hasMany(TestResult::class, 'doctor_id');
}


}
