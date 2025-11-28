<?php

namespace App\Notifications\Contracts;

interface FirebaseNotification
{
    /**
     * الحصول على بيانات الإشعار لـ Firebase
     */
    public function toFirebase($notifiable): array;
}


