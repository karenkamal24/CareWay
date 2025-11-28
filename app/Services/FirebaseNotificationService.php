<?php

namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * إرسال إشعار FCM إلى مستخدم واحد
     */
    public function sendToUser($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) {
            Log::warning('FCM token is missing');
            return false;
        }

        try {
            $messaging = Firebase::messaging();

            $notification = Notification::create($title, $body);

            // تحويل جميع قيم data إلى strings (Firebase يتطلب strings فقط)
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[$key] = (string) $value;
            }

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData($stringData);

            $result = $messaging->send($message);

            Log::info('✅ FCM notification sent successfully', [
                'token' => substr($fcmToken, 0, 20) . '...',
                'title' => $title,
                'body' => $body,
                'data' => $stringData,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('❌ Failed to send FCM notification', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'token' => substr($fcmToken, 0, 20) . '...',
                'title' => $title,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * إرسال إشعار FCM إلى عدة مستخدمين
     */
    public function sendToMultipleUsers(array $fcmTokens, $title, $body, $data = [])
    {
        $results = [];

        foreach ($fcmTokens as $token) {
            $results[] = $this->sendToUser($token, $title, $body, $data);
        }

        return $results;
    }

    /**
     * إرسال إشعار FCM إلى موضوع (topic)
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        try {
            $messaging = Firebase::messaging();

            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($data);

            $messaging->send($message);

            Log::info('FCM notification sent to topic', [
                'topic' => $topic,
                'title' => $title,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification to topic', [
                'error' => $e->getMessage(),
                'topic' => $topic,
            ]);

            return false;
        }
    }
}

