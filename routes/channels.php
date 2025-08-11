<?php
use Illuminate\Support\Facades\Broadcast;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\TestResult;


Broadcast::channel('chat.{appointmentId}', function ($user, $appointmentId) {
    $appointment = Appointment::find($appointmentId);

    if (!$appointment) {
        return false;
    }

    return $user->id === $appointment->user_id || $user->id === $appointment->doctor_id;
});



Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    return $user->id === Order::find($orderId)->user_id;
});

Broadcast::channel('test-results.{testId}', function ($user, $testId) {
    return $user->id === TestResult::find($testId)->user_id;
});
