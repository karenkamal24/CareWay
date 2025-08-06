<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected $apiUrl = 'https://accept.paymob.com/api';
    protected $authToken;

    public function authenticate()
    {
        $apiKey = env('PAYMOB_API_KEY');
        if (empty($apiKey)) {
            Log::error('PAYMOB_API_KEY is missing in .env file.');
            return null;
        }

        $response = Http::post("{$this->apiUrl}/auth/tokens", ['api_key' => $apiKey]);
        $data = $response->json();

        if ($response->successful()) {
            Log::info('Paymob Authentication Response', ['response' => $data]);
            $this->authToken = $data['token'] ?? null; // ✅ حفظ التوكن داخل الكلاس
            return $this->authToken;
        } else {
            Log::error('Paymob Authentication Failed', ['response' => $data]);
            return null;
    }}



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

        $orderData = $response->json();
        Log::info('استجابة Paymob - إنشاء الطلب', ['response' => $orderData]);

        return $orderData['id'] ?? null;
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

        $paymentData = $response->json();
        Log::info('استجابة Paymob - مفتاح الدفع', ['response' => $paymentData]);

        return $paymentData['token'] ?? null;
    }
    public function getTransactionStatus($paymobOrderId)
    {
        try {
            if (!$this->authToken) { // ✅ الآن authToken موجودة داخل الكلاس
                $this->authToken = $this->authenticate();
                if (!$this->authToken) {
                    throw new \Exception('فشل التوثيق مع Paymob');
                }
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->authToken}"
            ])->get("{$this->apiUrl}/acceptance/transactions", [
                'order_id' => $paymobOrderId
            ]);

            $data = $response->json();

            if (isset($data['transactions']) && count($data['transactions']) > 0) {
                return $data['transactions'][0]['success'] ? 'paid' : 'failed';
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('Paymob Transaction Status Error', ['error' => $e->getMessage()]);
            return 'error';
        }
    }
}
