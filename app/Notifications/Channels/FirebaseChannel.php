<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\FirebaseNotificationService;
use App\Notifications\Contracts\FirebaseNotification;
use Illuminate\Support\Facades\Log;

class FirebaseChannel
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * إرسال الإشعار عبر Firebase Cloud Messaging
     */
    public function send($notifiable, Notification $notification)
    {
        // التحقق من وجود FCM token
        if (!$notifiable->fcm_token) {
            Log::info('User does not have FCM token', [
                'user_id' => $notifiable->id,
            ]);
            return;
        }

        // الحصول على بيانات الإشعار
        if ($notification instanceof FirebaseNotification) {
            $message = $notification->toFirebase($notifiable);
        } else {
            // استخدام بيانات افتراضية إذا لم يكن هناك method toFirebase
            $message = [
                'title' => 'إشعار جديد',
                'body' => 'لديك إشعار جديد',
                'data' => [],
            ];
        }

        // إرسال الإشعار
        $this->firebaseService->sendToUser(
            $notifiable->fcm_token,
            $message['title'] ?? 'Notification',
            $message['body'] ?? '',
            $message['data'] ?? []
        );
    }
}

