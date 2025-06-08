<?php

namespace App\Services\AuthService;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Traits\SendMailTrait;
use App\Helpers\ApiResponseHelper;

class AuthService
{
    use SendMailTrait;
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken("API TOKEN")->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $data)
    {
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return null;
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return null;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): array
    {
        try {
            $user->tokens()->delete();
            return [
                'status' => true,
                'message' => 'Logged out successfully.',
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Failed to logout.',
            ];
        }
    }

    public function forgotPassword(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'status' => false,
                'message' => 'User not found',
                'code' => ApiResponseHelper::STATUS_NOT_FOUND,
            ];
        }

        try {
            $otp = rand(1000, 9999);

            $response = $this->sendEmail(
                $user->email,
                'Your authentication for CareWay Hospital',
                "Your authentication OTP is: $otp"
            );

            if ($response['status'] !== 200) {
                Log::error('Failed to send OTP email: ' . $response['message']);

                return [
                    'status' => false,
                    'message' => 'Failed to send OTP. Please try again later.',
                    'code' => ApiResponseHelper::STATUS_INTERNAL_SERVER_ERROR,
                ];
            }

            $user->update([
                'last_otp' => Hash::make($otp),
                'last_otp_expire' => Carbon::now()->addMinutes(10),
            ]);

            return [
                'status' => true,
                'message' => 'OTP sent to your email.',
                'code' => ApiResponseHelper::STATUS_OK,
            ];
        } catch (Exception $e) {
            Log::error('Exception in forgotPassword: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Unexpected error occurred.',
                'code' => ApiResponseHelper::STATUS_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    public function validateOtp(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return [
                'status' => false,
                'message' => 'User not found',
                'code' => 404,
            ];
        }

        if (Carbon::now()->gt($user->last_otp_expire)) {
            return [
                'status' => false,
                'message' => 'OTP has expired',
                'code' => 400,
            ];
        }

        if (!Hash::check($data['otp'], $user->last_otp)) {
            return [
                'status' => false,
                'message' => 'Invalid OTP',
                'code' => 400,
            ];
        }

        $user->update([
            'email_verified_at' => now(),
            'last_otp' => Hash::make(rand(1000, 9999)), // إعادة تعيين OTP بعد الاستخدام
        ]);

        return [
            'status' => true,
            'message' => 'OTP is valid, you can now reset your password',
            'code' => 200,
        ];
    }

    public function resetPassword(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return [
                'status' => false,
                'message' => 'User not found',
                'code' => 404,
            ];
        }

        if (Carbon::now()->gt($user->last_otp_expire)) {
            return [
                'status' => false,
                'message' => 'OTP has expired',
                'code' => 400,
            ];
        }

        if (!Hash::check($data['otp'], $user->last_otp)) {
            return [
                'status' => false,
                'message' => 'Invalid OTP',
                'code' => 400,
            ];
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'last_otp' => Hash::make(rand(1000, 9999)), // Reset OTP after use
            'last_otp_expire' => Carbon::now(),
        ]);

        return [
            'status' => true,
            'message' => 'Password reset successful',
            'code' => 200,
        ];
    }
}
