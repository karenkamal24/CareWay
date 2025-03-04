<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    protected function notificationData()
    {
        return [
            'message' => __("Your order #:order_id has been shipped!", ['order_id' => $this->order->id]),
            'order_id' => $this->order->id,
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationData();
    }

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
}
