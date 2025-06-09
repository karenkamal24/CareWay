<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Pharmacy\DeliveryService;

class PaymentController extends Controller
{
    protected $apiUrl = 'https://accept.paymob.com/api';

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
            return $data['token'] ?? null;
        } else {
            Log::error('Paymob Authentication Failed', ['response' => $data]);
            return null;
        }
    }

    public function createOrder($authToken, $totalPrice)
    {
        $response = Http::post("{$this->apiUrl}/ecommerce/orders", [
            'auth_token' => $authToken,
            'delivery_needed' => false,
            'amount_cents' => (int) ($totalPrice * 100),
            'currency' => 'EGP',
            'items' => [],
            'merchant_order_id' => time(),
        ]);

        $data = $response->json();
        Log::info('Paymob Create Order Response', ['response' => $data]);

        return $data;
    }

    public function getPaymentKey($authToken, $orderId, $amount, $billingData)
    {
        $response = Http::post("{$this->apiUrl}/acceptance/payment_keys", [
            'auth_token' => $authToken,
            'amount_cents' => (int) ($amount * 100),
            'expiration' => 3600,
            'order_id' => (int) $orderId,
            'billing_data' => $billingData,
            'currency' => 'EGP',
            'integration_id' => env('PAYMOB_INTEGRATION_ID'),
        ]);

        $data = $response->json();
        Log::info('Paymob Payment Key Response', ['response' => $data]);

        return $data['token'] ?? null;
    }

    public function storeCardOrder(Request $request, DeliveryService $deliveryService)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated!'], 401);
            }

            // استرجاع العناصر من السلة
            $cartItems = CartItem::whereHas('cart', fn($query) => $query->where('user_id', $user->id))->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Cart is empty!'], 400);
            }

            // حساب سعر التوصيل والإجمالي
            $distanceKm = $deliveryService->getDistanceFromPharmacy($request->latitude, $request->longitude);
            $deliveryFee = $deliveryService->calculateDeliveryFee($distanceKm);
            $totalPrice = $cartItems->sum(fn($item) => $item->price * $item->quantity);
            $finalPrice = $totalPrice + $deliveryFee;

            // بيانات الفاتورة
            $billingData = [
                "first_name" => $request->first_name ?? '',
                "last_name" => $request->last_name ?? '',
                "email" => $request->email ?? '',
                "phone_number" => $request->phone_number ?? '',
                "city" => $request->city ?? '',
                "country" => $request->country ?? '',
                "street" => $request->street ?? '',
                "building" => $request->building ?? '',
                "floor" => $request->floor ?? '',
                "apartment" => $request->apartment ?? '',
            ];

            // إنشاء الطلب
            $order = Order::create([
                'user_id' => $user->id,
                'name' => "{$billingData['first_name']} {$billingData['last_name']}",
                'phone' => $billingData['phone_number'],
                'address' => "{$billingData['street']}, {$billingData['building']}, {$billingData['floor']}, {$billingData['apartment']}, {$billingData['city']}, {$billingData['country']}",
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'total_price' => $finalPrice,
                'payment_method' => 'card',
                'status' => 'pending',
                'payment_state' => 'initiated', // تم إضافة payment_state
            ]);

            // Paymob API Integration
            $authToken = $this->authenticate();
            if (!$authToken) throw new \Exception("Authentication failed");

            $paymobOrder = $this->createOrder($authToken, $finalPrice);
            if (!isset($paymobOrder['id'])) throw new \Exception("Failed to create Paymob order");

            $order->paymob_order_id = $paymobOrder['id'];
            $order->save();

            $paymentToken = $this->getPaymentKey($authToken, $paymobOrder['id'], $finalPrice, $billingData);
            if (!$paymentToken) throw new \Exception("Failed to generate payment token");

            // تقليل الكمية لكل منتج في السلة
            foreach ($cartItems as $cartItem) {
                $medicine = Product::find($cartItem->medicine_id);
                if ($medicine) {
                    $medicine->quantity -= $cartItem->quantity;
                    $medicine->save();
                }
            }

            // حذف العناصر من السلة
            CartItem::whereHas('cart', fn($query) => $query->where('user_id', $user->id))->delete();

            $iframeId = env('PAYMOB_IFRAME_ID');
            $iframeUrl = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully! Proceed to payment.',
                'order_id' => $order->id,
                'paymob_order_id' => $paymobOrder['id'],
                'iframe_url' => $iframeUrl,
                'total_price' => $finalPrice,
                'delivery_fee' => $deliveryFee,
                'payment_method' => 'card',
                'status' => 'pending',
                'payment_state' => 'initiated', // تم تضمين payment_state
                'billing_data' => $billingData
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function handleWebhook(Request $request)
    {
        Log::info('Paymob Webhook Received', ['data' => $request->all()]);

        $paymobOrderId = $request->input('obj.order.id');
        $transactionStatus = $request->input('obj.success');

        $order = Order::where('paymob_order_id', $paymobOrderId)->first();
        if (!$order) {
            Log::error('Order not found for Paymob Order ID', ['paymob_order_id' => $paymobOrderId]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($transactionStatus) {

            $order->payment_state = 'completed';
            Log::info('Payment successful', ['order_id' => $order->id]);
        } else {

            $order->payment_state = 'failed';
            Log::warning('Payment failed', ['order_id' => $order->id]);
        }

        $order->save();
        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }




}
