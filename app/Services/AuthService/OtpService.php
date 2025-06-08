<?php

namespace App\Services\AuthService;

use App\Models\User;
use App\Traits\SendMailTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class OtpService
{
    use SendMailTrait;

    /**
     * Generate OTP for a user, send email and update user record.
     *
     * @param User $user
     * @param int $expiryMinutes
     * @return array
     * @throws \Exception
     */
    public function generateOtpForUser(User $user, int $expiryMinutes = 5): array
    {
        $otp = rand(1000, 9999);

        $title = "Your authentication for application CareWay Hospital";
        $body = "Your authentication OTP is: $otp";

        $response = $this->sendEmail($user->email, $title, $body);

        if ($response['status'] !== 200) {
            Log::error('Failed to send OTP email to ' . $user->email . ': ' . $response['message']);
            throw new Exception('Failed to send OTP email');
        }

        $user->update([
            'last_otp' => Hash::make($otp),
            'last_otp_expire' => Carbon::now()->addMinutes($expiryMinutes),
        ]);

        return [
            'status' => true,
            'message' => 'OTP sent to your email',
        ];
    }
}
