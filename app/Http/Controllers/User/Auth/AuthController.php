<?php

namespace App\Http\Controllers\User\Auth;
use App\Traits\SendMailTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\AuthService\AuthService;
use App\Services\AuthService\OtpService;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ValidateOtpRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Auth\ResetPasswordRequest;
class AuthController extends Controller
{
    use SendMailTrait;
    protected $AuthService;
    protected $otpService;

    public function __construct(AuthService $AuthService, OtpService $otpService)
    {
    $this->AuthService = $AuthService;
    $this->otpService = $otpService;
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $result = $this->AuthService->register($data);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'token' => $result['token'],
            'user' => $result['user'],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->AuthService->login($data);

        if (!$result) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $result['token'],
            'user' => $result['user'],
        ]);
    }
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No authenticated user found'
            ], 401);
        }

        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();

            return response()->json([
                'message' => 'Logout successful'
            ], 200);
        }

        return response()->json([
            'message' => 'No valid access token found'
        ], 401);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        $otpService = new OtpService();
        $otp = $otpService->generateOtpForUser($user);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your email.',
        ], 200);
    }

public function validateOtpForPasswordReset(ValidateOtpRequest $request, AuthService $AuthService)
{
    $response = $AuthService->validateOtp($request->validated());

    return response()->json([
        'status' => $response['status'],
        'message' => $response['message'],
    ], $response['code']);
}

public function resetPassword(ResetPasswordRequest $request, AuthService $AuthService)
{
    $response = $AuthService->resetPassword($request->validated());

    return response()->json([
        'status' => $response['status'],
        'message' => $response['message'],
    ], $response['code']);
}



}
