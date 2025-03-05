<?php

namespace App\Http\Controllers\User;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Services\PaymobService;


class PaymentController extends Controller
{
    protected $apiUrl = 'https://accept.paymob.com/api';

    // Step 1: Authenticate with Paymob
    public function authenticate()
    {
        $response = Http::post("{$this->apiUrl}/auth/tokens", [
            'api_key' => env('PAYMOB_API_KEY'),
        ]);

        return $response->json()['token'];
    }

    // Step 2: Create Order
    public function createOrder($authToken, $amount)
    {
        $response = Http::post("{$this->apiUrl}/ecommerce/orders", [
            'auth_token' => $authToken,
            'delivery_needed' => false,
            'amount_cents' => $amount * 100, 
            'currency' => 'EGP',
            'merchant_order_id' => uniqid(),
            'items' => []
        ]);

        return $response->json();
    }

    // Step 3: Generate Payment Key
    public function getPaymentKey($authToken, $orderId, $amount, $billingData)
    {
        $response = Http::post("{$this->apiUrl}/acceptance/payment_keys", [
            'auth_token' => $authToken,
            'amount_cents' => $amount * 100,
            'expiration' => 3600,
            'order_id' => $orderId,
            'billing_data' => $billingData,
            'currency' => 'EGP',
            'integration_id' => env('PAYMOB_INTEGRATION_ID'),
        ]);

        return $response->json();
    }

    // Step 4: Handle Payment Request from Mobile App


    // ...existing code...
    
    // Step 4: Handle Payment Request from Mobile App
    public function pay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'street' => 'required|string',
            'building' => 'required|string',
            'floor' => 'required|string',
            'apartment' => 'required|string',
        ]);
    
        try {
            $authToken = $this->authenticate();
            $order = $this->createOrder($authToken, $request->amount);
    
            if (!isset($order['id'])) {
                Log::error('Failed to create order', ['response' => $order]);
                return response()->json(['error' => 'Failed to create order'], 500);
            }
    
            // Billing details
            $billingData = $request->only([
                'first_name', 'last_name', 'email', 'phone_number',
                'city', 'country', 'street', 'building', 'floor', 'apartment'
            ]);
    
            $paymentKey = $this->getPaymentKey($authToken, $order['id'], $request->amount, $billingData);
    
            if (!isset($paymentKey['token'])) {
                Log::error('Failed to generate payment key', ['response' => $paymentKey]);
                return response()->json(['error' => 'Failed to generate payment key'], 500);
            }
    
            // Return response to mobile app
            return response()->json([
                'iframe_url' => "https://accept.paymob.com/api/acceptance/iframes/" . env('PAYMOB_IFRAME_ID') . "?payment_token=" . $paymentKey['token']
            ]);
        } catch (\Exception $e) {
            Log::error('Payment processing error', ['exception' => $e]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    
    // ...existing code...
    // Step 5: Handle Webhook Response from Paymob
    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        if (isset($data['success']) && $data['success']) {
            // Payment successful, update your database
            return response()->json(['message' => 'Payment successful']);
        }

        return response()->json(['message' => 'Payment failed'], 400);
    }
}