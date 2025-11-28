<?php
// filepath: /d:/project_graduation/app/Notifications/OrderShippedNotification.php


namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Order;
use App\Notifications\Channels\FirebaseChannel;
use App\Notifications\Contracts\FirebaseNotification;
use Illuminate\Support\Facades\Log;

class OrderShippedNotification extends Notification implements FirebaseNotification
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        // فقط قاعدة البيانات والبريد - Firebase يتم إرساله مباشرة في Order model
        return ['mail', 'database'];
    }

    /**
     * إشعار البريد الإلكتروني
     */
    public function toMail($notifiable)
    {
        Log::info("Sending email notification to: " . $notifiable->email);

        return (new MailMessage)
            ->subject('Your Order Has Been Shipped!')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your order #' . $this->order->id . ' has been shipped.')
            ->line('Thank you for shopping with us!');
    }

    /**
     * بيانات الإشعار الموحدة
     */
    protected function notificationData()
    {
        return [
            'message' => __("Your order #:order_id has been shipped!", ['order_id' => $this->order->id]),
            'order_id' => $this->order->id,
        ];
    }

    /**
     * إشعار قاعدة البيانات
     */
    public function toDatabase($notifiable)
    {
        return $this->notificationData();
    }

    /**
     * إشعار البث المباشر
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->notificationData());
    }

    public function broadcastOn()
    {
        return ['private-orders.' . $this->order->id];
    }

    public function broadcastAs()
    {
        return 'order.shipped';
    }

    /**
     * إشعار Firebase Cloud Messaging
     */
    public function toFirebase($notifiable): array
    {
        return [
            'title' => 'تم شحن طلبك!',
            'body' => "تم شحن طلبك رقم #{$this->order->id} بنجاح.",
            'data' => [
                'type' => 'order_shipped',
                'order_id' => $this->order->id,
                'message' => $this->notificationData()['message'],
            ],
        ];
    }
}
