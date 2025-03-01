<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\SendMailTrait;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class User extends Authenticatable
{    use SendMailTrait;
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
            'date_of_birth' => 'date',
            'last_otp_expire' => 'datetime',
        ];
    }
    public function generateOtp($expiryMinutes = 10)
    {

        $otp = rand(1000, 9999); 
        $title = "Your authentication for application CareWay Hospital ";
        $body = "Your authentication OTP is: $otp";
        $this->sendEmail($this->email, $title, $body);
        $this->update([
            'last_otp' => Hash::make($otp),
            'last_otp_expire' => Carbon::now()->addMinutes($expiryMinutes), 
        ]);
        return $otp;
    }
}
