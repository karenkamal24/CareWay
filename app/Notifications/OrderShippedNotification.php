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
use Illuminate\Support\Facades\Log;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
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
    }}