<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Auth\AuthController;
use App\Http\Controllers\User\Pharmacy\CategoryController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\Pharmacy\MedicineController;
use App\Http\Controllers\User\Pharmacy\CartController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\PaymentController;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\User\BannerController;

use Intervention\Image\Facades\Image;
use App\Http\Controllers\User\TestResultController;
use App\Http\Controllers\User\OCRController;


use App\Http\Controllers\User\DoctorController;
use App\Http\Controllers\User\DepartmentController;
use App\Http\Controllers\User\AppointmentController;
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
         Route::post('/card', [OrderController::class, 'storeOrderCard']);
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

    Route::post('/paymob/pay', [PaymentController::class, 'storeCardOrder']);
    //test lab
    Route::get('/test-results', [TestResultController::class, 'index']);
    //Doctor
    Route::prefix('doctors')->group(function () {
    Route::get('/', [DoctorController::class, 'index']);
    Route::get('/{id}', [DoctorController::class, 'show']);
    });
    //Department
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::get('/{id}', [DepartmentController::class, 'show']);
    });

    Route::prefix('appointments')->middleware('auth')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/store', [AppointmentController::class, 'storeAppointment'])->name('appointments.store');
        Route::put('/update/{id}', [AppointmentController::class, 'updateAppointment'])->name('appointments.update');
        Route::delete('/delete/{id}', [AppointmentController::class, 'deleteAppointment'])->name('appointments.delete');
    });






});






//Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class,'forgotPassword']);
Route::post('/validateOtpForPasswordReset', [AuthController::class, 'validateOtpForPasswordReset']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
//categories
Route::prefix('categories')->group(function () {
    Route::get('/main', [CategoryController::class, 'main']);
    Route::get('/{id}/subcategories', [CategoryController::class, 'subcategories']);
    Route::get('/{id}/products', [CategoryController::class, 'products']);
});
//Medicine
Route::prefix('medicines')->group(function () {
    Route::get('/', [MedicineController::class, 'index']);
    Route::get('/{id}', [MedicineController::class, 'show']);
    Route::get('/latest', [MedicineController::class, 'latest']);
});
Route::post('/paymob/webhook', [PaymentController::class, 'handleWebhook']);
// Route::post('/paymob/webhook', [AppointmentController::class, 'paymobWebhook']);


// Route::post('/ocr', function (Request $request) {
//     if (!$request->hasFile('image')) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Image is required',
//         ], 422);
//     }

//     try {
//         $image = $request->file('image');

//         // ⬛️ تعديل الصورة قبل الإرسال (رمادي + تغيير الحجم + جودة)
//         $processedImage = Image::make($image)
//             ->greyscale()
//             ->resize(1200, null, function ($constraint) {
//                 $constraint->aspectRatio();
//                 $constraint->upsize();
//             })
//             ->encode('jpg', 90);

//         // ⬛️ إرسال للصورة للـ ngrok OCR
//         $response = Http::timeout(60)
//             ->withoutVerifying()
//             ->attach(
//                 'image',
//                 $processedImage->getEncoded(),
//                 'processed.jpg'
//             )
//             ->post('https://32a3-35-245-176-184.ngrok-free.app/ocr');

//         $ocrText = $response->json()['text'] ?? '';
//         $lines = explode("\n", $ocrText);

//         // ⬛️ تنظيف النتيجة (حذف سطور فاضية أو رموز)
//         $cleaned = array_filter(array_map(function ($line) {
//             $line = trim($line);
//             // تجاهل السطر لو قصير أو مليان رموز فقط
//             return (strlen($line) > 2 && preg_match('/[a-zA-Z0-9]/', $line)) ? $line : null;
//         }, $lines));

//         return response()->json([
//             'status' => true,
//             'lines' => array_values($cleaned),
//             'text' => implode(' ', $cleaned), // النتيجة كلها في سطر واحد لو حابة
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'error' => $e->getMessage()
//         ]);
//     }
// });


Route::get('/banners', [BannerController::class, 'index']);
