<?php

namespace App\Http\Controllers\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\OrderShippedEvent;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json([
            'notifications' => Auth::user()->notifications
        ]);
    }

    public function unread()
    {
        return response()->json([
            'unread_notifications' => Auth::user()->unreadNotifications
        ]);
    }
    public function markAsRead($id)
    {
        $notification = DatabaseNotification::find($id);
    
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
    
        if ($notification->notifiable_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $notification->markAsRead();
        return response()->json(['message' => 'Notification marked as read']);
    }
   
    
}
