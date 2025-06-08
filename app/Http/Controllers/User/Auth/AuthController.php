<?php

namespace App\Http\Controllers\User\Auth;

use App\Traits\SendMailTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService\AuthService;
use App\Services\AuthService\OtpService;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ValidateOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Helpers\ApiResponseHelper;
use App\Models\User;

class AuthController extends Controller
{
    use SendMailTrait;

    protected AuthService $authService;
    protected OtpService $otpService;

    public function __construct(AuthService $authService, OtpService $otpService)
    {
        $this->authService = $authService;
        $this->otpService = $otpService;
    }

    public function register(RegisterRequest $request)
    {
        $response = $this->authService->register($request->validated());

        return ApiResponseHelper::success(
            'User registered successfully.',
            ['user' => $response['user'], 'token' => $response['token']],
            ApiResponseHelper::STATUS_CREATED
        );
    }

    public function login(LoginRequest $request)
    {
        $response = $this->authService->login($request->validated());

        if (!$response) {
            return ApiResponseHelper::unauthorized('Invalid email or password');
        }

        return ApiResponseHelper::success(
            'Login successful',
            ['user' => $response['user'], 'token' => $response['token']]
        );
    }

    public function logout(Request $request)
    {
        $result = $this->authService->logout($request->user());

        if ($result['status'] === false) {
            return ApiResponseHelper::unauthorized($result['message']);
        }

        return ApiResponseHelper::success($result['message']);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $result = $this->authService->forgotPassword($request->email);

        if ($result['status']) {
            return apiResponse()->success($result['message'], null, $result['code']);
        }

        return apiResponse()->error($result['message'], $result['code']);
    }

    public function validateOtpForPasswordReset(ValidateOtpRequest $request)
    {
        $response = $this->authService->validateOtp($request->validated());

        if (!$response['status']) {
            return ApiResponseHelper::error($response['message'], $response['code']);
        }

        return ApiResponseHelper::success($response['message']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $response = $this->authService->resetPassword($request->validated());

        if (!$response['status']) {
            return ApiResponseHelper::error($response['message'], $response['code']);
        }

        return ApiResponseHelper::success($response['message']);
    }
}
