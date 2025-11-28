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
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications->count(),
        ]);
    }

    public function unread()
    {
        $user = Auth::user();

        $unreadNotifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'unread_notifications' => $unreadNotifications,
            'unread_count' => $unreadNotifications->count(),
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

    /**
     * حفظ أو تحديث FCM Token للمستخدم
     */
    public function updateFcmToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();
        $user->fcm_token = $validated['fcm_token'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully',
        ]);
    }


}
