<?php
use App\Models\Doctor;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\Pharmacy\ProductResource;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-env', function () {
    return env('PAYMOB_API_KEY', 'Not Found');
});

Route::get('/test-notification', function () {
    $admins = User::where('user_type', 'admin')->get();

    foreach ($admins as $admin) {
        Notification::make()
            ->title('إشعار تجريبي')
            ->body('هذا إشعار مباشر لاختبار البث عبر Laravel Echo.')
            ->success()
            ->actions([
                Action::make('عرض المنتج')
                    ->url(ProductResource::getUrl())
                    ->color('primary'),
            ])
            ->sendToDatabase($admin);
    }

    return 'تم إرسال الإشعار!';
});
Route::view('/processing', 'processing')->name('processing');





Route::get('/send-notification', function (Messaging $messaging) {

    $deviceToken = 'fkP6xnlsQ8mzk-skRCaUyG:APA91bGtk89_bewckLl4qr26VM2_oTgm95KINxObD6hJl8ffA_05Yd3Xki9Ckt8xo1cCxlKLNNRPBevYdo9HnNTqEd4_YbNRsPMIQ9clTD3viMEgJQni13c';

    try {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification([
                'title' => 'Firebase Test',
                'body'  => 'Your Laravel Firebase Notification Works 🚀',
            ])
            ->withData([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // لو بتستخدم Flutter
                'custom_key'   => 'custom_value'
            ]);

        $messaging->send($message);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
