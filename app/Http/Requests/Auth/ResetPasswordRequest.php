<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
            'otp' => 'required|digits:4',
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'This email is not registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and symbol.',
            'otp.digits' => 'OTP must be 4 digits.',
        ];
    }
}
