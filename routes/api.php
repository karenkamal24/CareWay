<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\MedicineController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//Authentication 
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class,'forgotPassword']);
Route::post('/validateOtpForPasswordReset', [AuthController::class, 'validateOtpForPasswordReset']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
//category
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']); 
});
//Medicine
Route::prefix('medicines')->group(function () {
    Route::get('/', [MedicineController::class, 'index']); 
    Route::get('/{id}', [MedicineController::class, 'show']);
});