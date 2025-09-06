<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\AvailableDoctor;
use Illuminate\Support\Facades\Log;
use App\Services\PaymobService;
class AppointmentController extends Controller
{

    protected $paymobService;

    public function __construct(PaymobService $paymobService)
    {
        $this->paymobService = $paymobService;
    }

public function storeCashAppointment(Request $request)
{
    DB::beginTransaction();
    try {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Authentication required'], 401);

        $availableDoctor = AvailableDoctor::findOrFail($request->available_doctor_id);

        if ($availableDoctor->booked_count >= $availableDoctor->capacity) {
            return response()->json(['error' => 'No available slots!'], 400);
        }

        $doctor = Doctor::findOrFail($availableDoctor->doctor_id);

        $appointment = Appointment::create([
            'user_id'             => $user->id,
            'doctor_id'           => $doctor->id,
            'available_doctor_id' => $availableDoctor->id,
            'type'                => $availableDoctor->type,
            'appointment_time'    => $availableDoctor->start_time, // لو عايز تدمج اليوم والوقت ممكن تضيف day_of_week
            'payment_status'      => 'cash',
            'amount'              => $doctor->price,
            'payment_method'      => 'cash',
        ]);

        // تحديث booked_count و is_booked
        $availableDoctor->increment('booked_count');
        if ($availableDoctor->booked_count >= $availableDoctor->capacity) {
            $availableDoctor->is_booked = true;
            $availableDoctor->save();
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Appointment successfully booked! Please pay at the clinic.'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




    // public function paymobWebhook(Request $request)
    // {
    //     Log::info(' Paymob Webhook received', $request->all());

    //     try {
    //         $data = $request->all();

    //         if (!isset($data['obj'])) {
    //             Log::error(' Invalid Webhook: Missing transaction data.');
    //             return response()->json(['error' => 'Invalid Webhook'], 400);
    //         }

    //         $transaction = $data['obj'];
    //         $orderId = $transaction['order']['id'] ?? null;
    //         $success = $transaction['success'] ?? false;

    //         if (!$orderId) {
    //             Log::error('Invalid Webhook: Missing order_id.');
    //             return response()->json(['error' => 'Invalid order ID'], 400);
    //         }

    //         // Find appointment by paymob_order_id
    //         $appointment = Appointment::where('paymob_order_id', $orderId)->first();

    //         if (!$appointment) {
    //             Log::error(' Appointment not found.', ['order_id' => $orderId]);
    //             return response()->json(['error' => 'Appointment not found'], 404);
    //         }

    //         // Update payment status
    //         if ($success) {
    //             $appointment->payment_status = 'completed';
    //             Log::info(' Payment successful!', ['order_id' => $orderId]);
    //         } else {
    //             $appointment->payment_status = 'failed';
    //             Log::warning(' Payment failed.', ['order_id' => $orderId]);
    //         }

    //         $appointment->save();

    //         return response()->json(['message' => 'Webhook processed successfully'], 200);
    //     } catch (\Exception $e) {
    //         Log::error(' Webhook processing error', ['error' => $e->getMessage()]);
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }



}
