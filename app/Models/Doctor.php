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

    protected $fillable = ['department_id', 'user_id', 'name', 'email', 'password', 'phone', 'description', 'specialization', 'price', 'image', 'status'];

    protected $hidden = ['password'];
    protected $with = ['roles'];


    protected $guard_name = 'doctor';


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function availableAppointments()
    {
        return $this->hasMany(Available_doctor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); 
    }
    
    public function testResults()
    {
        return $this->hasMany(TestResult::class, 'doctor_id');
    }

    public function scopeWithRoles(Builder $query): Builder
    {
        return $query->with('roles');
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->hasRole('doctor');
    }
 
    


}
