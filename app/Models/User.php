<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\SendMailTrait;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Notifications\DatabaseNotification;

class User extends Authenticatable implements FilamentUser
{    use SendMailTrait;
    use HasRoles;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'gender',
        'date_of_birth',
        'last_otp',
        'last_otp_expire',
        'email_verified_at',
        'password',
        'image',
        'user_type',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_otp_expire' => 'datetime',
            'date_of_birth' => 'date:Y-m-d',  // يعرض فقط تاريخ بدون وقت
            'created_at' => 'datetime:Y-m-d H:i:s', // يمكن تعديل التنسيق حسب الحاجة
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
{
    if ($panel->getId() === 'admin') {
        return $this->user_type === 'admin';
    }

    return true;
}
public function notifications()
{
    return $this->morphMany(DatabaseNotification::class, 'notifiable')->latest();
}

public function testResults()
{
    return $this->hasMany(TestResult::class, 'patient_id');
}


}
