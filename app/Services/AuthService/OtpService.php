<?php

namespace App\Services\AuthService;

use App\Models\User;
use App\Traits\SendMailTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class OtpService
{
    use SendMailTrait;

    /**
     * Generate OTP for a user, send email and update user record.
     *
     * @param User $user
     * @param int $expiryMinutes
     * @return int Generated OTP
     */
    public function generateOtpForUser(User $user, int $expiryMinutes = 5): int
    {
        $otp = rand(1000, 9999);

        $title = "Your authentication for application CareWay Hospital";
        $body = "Your authentication OTP is: $otp";


        $this->sendEmail($user->email, $title, $body);


        $user->update([
            'last_otp' => Hash::make($otp),
            'last_otp_expire' => Carbon::now()->addMinutes($expiryMinutes),
        ]);

        return $otp;
    }
}
