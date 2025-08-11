<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use SerializesModels, InteractsWithSockets;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender', 'receiver');
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.' . $this->message->appointment_id);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
