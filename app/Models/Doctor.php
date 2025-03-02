<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;


class Doctor  extends User
{
    use HasFactory, Notifiable;

    protected $fillable = ['department_id','user_id','name', 'email', 'password', 'phone', 'description', 'specialization', 'price', 'image', 'status'];

    protected $hidden = ['password'];

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
        return $this->belongsTo(User::class);
    }
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }
}