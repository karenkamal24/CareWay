<?php

namespace App\Http\Requests\Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
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
            'name' => [
                'required',
                'regex:/^([\p{L}]+\s+[\p{L}]+\s+[\p{L}]+)$/u',
                'max:255',
                'string'
            ],
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|min:10|max:15|unique:users',
            'address' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date|before:today',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
                'confirmed'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            "name.regex" => "The full name must contain at least 3 words.",
            "password.regex" => 'Password must contain lowercase, uppercase, number, and special character.',
            'password.confirmed' => 'Password confirmation does not match.',
            'gender.in' => 'Gender must be either male or female.',
            'date_of_birth.before' => 'Date of birth must be a past date.',
        ];
    }
}
