<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Message;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function index(Appointment $appointment)
    {
        $messages = Message::where('appointment_id', $appointment->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'attachment' => 'nullable|file',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $message = Message::create([
            'appointment_id' => $validated['appointment_id'],
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'] ?? '',
            'attachment' => $attachmentPath,
            'seen' => false,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }
}
