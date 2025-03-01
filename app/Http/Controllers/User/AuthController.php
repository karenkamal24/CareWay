<?php

namespace App\Http\Controllers\User;
use App\Traits\SendMailTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;
class AuthController extends Controller
{ 
    use SendMailTrait;
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
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
            ], [
                "name.regex" => "The full name must contain at least 3 words.",
                "password.regex" => 'Password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
                'password.confirmed' => 'Password confirmation does not match.',
                'gender.in' => 'Gender must be either male or female.',
                'date_of_birth.before' => 'Date of birth must be a past date.',
            ]);
            
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->first()
                ], 422);
            }
    
           
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'password' => Hash::make($request->password),

            ]);
           

    
    
            $token = $user->createToken("API TOKEN")->plainTextToken;
    
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully.',
                'token' => $token,
                'user' => $user
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while registering the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
}
public function login(Request $request ){
    $validator = Validator::make($request->all(),[
        'email'      => 'required|email',
        'password' => 'required','string',
        ]);
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()->first()
        ], 422);
    }
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'message' => 'Invalid email or password',
        ], 401);
    }
        $user= User::where('email',$request->email)->first();
        if(!$user){
            return response()->json([
                "success" => false,
                "message" => "user not found"
            ], 404);
        }
        $token=$user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
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
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);
    $user = User::where('email', $request->email)->first();
    $user->generateOtp();
    return response()->json([
        'status' => true,
        'message' => 'OTP sent to your email.',
    ], 200);
}



public function validateOtpForPasswordReset(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email', 
        'otp' => 'required|digits:4', 
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()->first(),
        ], 422);
    }

    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ], 404);
    }

    
    if (Carbon::now()->gt($user->last_otp_expire)) {
        return response()->json([
            'status' => false,
            'message' => 'OTP has expired',
        ], 400);
    }


    if (!Hash::check($request->otp, $user->last_otp)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP',
        ], 400);
    }

 
    $user->update([
        'email_verified_at' => now(), 
        'last_otp' => Hash::make(rand(1000, 9999)),
    ]);

    return response()->json([
        'status' => true,
        'message' => 'OTP is valid, you can now reset your password',
    ], 200);
}

public function resetPassword(Request $request)
{
    
    $validator = Validator::make($request->all(), [
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
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()->first(),
        ], 422);
    }

  
    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ], 404);
    }


    if (Carbon::now()->gt($user->last_otp_expire)) {
        return response()->json([
            'status' => false,
            'message' => 'OTP has expired',
        ], 400);
    }


    if (!Hash::check($request->otp, $user->last_otp)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP',
        ], 400);
    }

   
    $user->update([
        'password' => Hash::make($request->password),
        'last_otp' => Hash::make(rand(1000, 9999)), 
        'last_otp_expire' => Carbon::now(), 
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Password reset successful',
    ], 200);
}



}