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
{protected $paymobService;

    public function __construct(PaymobService $paymobService)
    {
        $this->paymobService = $paymobService;
    }

    public function storeAppointment(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info('New appointment request', $request->all());

            $user = Auth::user();
            if (!$user) {
                Log::error('Booking failed: User not authenticated');
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $availableDoctor = AvailableDoctor::findOrFail($request->available_doctor_id);
            if ($availableDoctor->is_booked) {
                Log::warning('Appointment already booked!', ['available_doctor_id' => $availableDoctor->id]);
                return response()->json(['error' => 'Appointment already booked!'], 400);
            }

            $doctor = Doctor::findOrFail($availableDoctor->doctor_id);
            $amount = $doctor->price;
            $type = $availableDoctor->type;

            $paymentMethod = $request->has('pay_with_card') ? 'card' : 'cash';


            if ($type === 'online' && $paymentMethod !== 'card') {
                return response()->json(['error' => 'Online appointments must be paid by card.'], 422);
            }

            $appointment = Appointment::create([
                'user_id' => $user->id,
                'doctor_id' => $doctor->id,
                'available_doctor_id' => $availableDoctor->id,
                'type' => $type,
                'appointment_time' => $availableDoctor->day . ' ' . $availableDoctor->start_time,
                'payment_status' => ($paymentMethod == 'card') ? 'pending' : 'cash',
                'amount' => $amount,
                'payment_method' => $paymentMethod,
            ]);

            $availableDoctor->is_booked = true;
            $availableDoctor->save();

            Log::info('Appointment successfully created', ['appointment_id' => $appointment->id]);

            if ($paymentMethod == 'card') {
                $authToken = $this->paymobService->authenticate();
                if (!$authToken) {
                    throw new \Exception('Failed to retrieve Paymob auth token');
                }

                $paymobOrderId = $this->paymobService->createOrder($authToken, $amount);
                if (!$paymobOrderId) {
                    throw new \Exception('Failed to create Paymob order');
                }

                $paymentToken = $this->paymobService->getPaymentKey($authToken, $paymobOrderId, $amount, $request->billing_data);
                if (!$paymentToken) {
                    throw new \Exception('Failed to get Paymob payment key');
                }

                $appointment->paymob_order_id = $paymobOrderId;
                $appointment->save();

                Log::info('Paymob payment request created', ['paymob_order_id' => $paymobOrderId, 'payment_token' => $paymentToken]);

                DB::commit();

                $iframeId = env('PAYMOB_IFRAME_ID');
                if (!$iframeId) {
                    throw new \Exception('PAYMOB_IFRAME_ID is missing in .env');
                }

                $iframeUrl = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";
                Log::info('Redirecting to Paymob Iframe', ['url' => $iframeUrl]);

                return response()->json([
                    'success' => true,
                    'message' => 'Appointment created successfully, please complete the payment.',
                    'payment_url' => $iframeUrl,
                    'paymob_order_id' => $paymobOrderId,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Appointment successfully booked! Please pay at the clinic.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking failed', ['error' => $e->getMessage()]);
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
