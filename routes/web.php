<?php
use App\Models\Doctor;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\Pharmacy\ProductResource;

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
