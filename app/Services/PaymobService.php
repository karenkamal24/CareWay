<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymobService
{
    protected $apiUrl = 'https://accept.paymob.com';

    public function authenticate()
    {
        $response = Http::post("{$this->apiUrl}/auth/tokens", [
            'api_key' => env('BAYHOB_API_KEY'),
        ]);

        return $response->json()['token'];
    }
    public function createOrder($authToken, $amount, $currency = 'EGP')
{
    $response = Http::post("{$this->apiUrl}/ecommerce/orders", [
        'auth_token' => $authToken,
        'delivery_needed' => false,
        'amount_cents' => $amount * 100, 
        'currency' => $currency,
        'merchant_order_id' => uniqid(),
        'items' => []
    ]);

    return $response->json();
}
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

}