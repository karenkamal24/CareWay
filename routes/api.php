<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\MedicineController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\PaymentController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//Authentication 
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);
    //cart
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'addItem']);
        Route::put('/update/{id}', [CartController::class, 'updateItem']); 
        Route::delete('/remove/{id}', [CartController::class, 'removeItem']); 
        Route::delete('/clear', [CartController::class, 'clearCart']); 
    });
    //order
    Route::prefix('order')->group(function () {
        Route::post('/cash', [OrderController::class, 'storeCashOrder']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::get('/', [OrderController::class, 'index']);
        Route::delete('/{id}', [OrderController::class, 'delete']);
    });
    //notification
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']); 
        Route::get('/unread', [NotificationController::class, 'unread']); 
        Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead']);
    });
  

 
    
});
//Authentication
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
// order
Route::get('/delivery-zones', [OrderController::class, 'getZones']);

Route::post('/paymob/pay', [PaymentController::class, 'pay']);
Route::post('/paymob/webhook', [PaymentController::class, 'handleWebhook']);