<?php

namespace App\Services\Pharmacy;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Pharmacy\DeliveryService;
use Exception;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class OrderService
{
    public function storeCashOrder(array $data, DeliveryService $deliveryService): array
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $name = !empty(trim($data['name'] ?? '')) ? $data['name'] : $user->name;
            $phone = !empty(trim($data['phone'] ?? '')) ? $data['phone'] : $user->phone;
            $address = !empty(trim($data['address'] ?? '')) ? $data['address'] : $user->address;

            $cartItems = CartItem::whereHas('cart', fn($query) => $query->where('user_id', $user->id))->get();

            if ($cartItems->isEmpty()) {
                return ['success' => false, 'message' => 'Cart is empty!'];
            }

            $distanceKm = $deliveryService->getDistanceFromPharmacy($data['latitude'], $data['longitude']);
            $deliveryFee = $deliveryService->calculateDeliveryFee($distanceKm);
            $totalPrice = $cartItems->sum(fn($item) => $item->price * $item->quantity);
            $finalPrice = $totalPrice + $deliveryFee;

            $order = Order::create([
                'user_id' => $user->id,
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'distance' => $distanceKm,
                'delivery_fee' => $deliveryFee,
                'total_price' => $finalPrice,
                'payment_method' => 'cash',
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'medicine_id' => $item->medicine_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

                $medicine = Product::find($item->medicine_id);

                if ($medicine) {
                    $medicine->quantity -= $item->quantity;
                    $medicine->save();

                    // إشعار عند انخفاض الكمية
                    if ($medicine->quantity <= 3) {
                        $admins = \App\Models\User::where('user_type', 'admin')->get();

                        foreach ($admins as $admin) {
                            FilamentNotification::make()
                                ->title('كمية منخفضة')
                                ->body("المنتج '{$medicine->name}' الكمية المتبقية: {$medicine->quantity}")
                                ->warning()
                                ->actions([
                                    Action::make('view')
                                        ->label('عرض المنتج')
                                        ->url(\App\Filament\Resources\Pharmacy\ProductResource::getUrl('edit', ['record' => $medicine]))
                                        ->color('primary'),
                                ])
                                ->sendToDatabase($admin);
                        }
                    }
                }
            }

            // حذف عناصر السلة بعد الطلب
            CartItem::whereHas('cart', fn($query) => $query->where('user_id', $user->id))->delete();

            DB::commit();

            return [
                'success' => true,
                'order' => $order,
                'user_info' => compact('name', 'phone', 'address'),
                'distance' => round($distanceKm, 2),
                'delivery_fee' => $deliveryFee,
                'final_price' => $finalPrice,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Something went wrong!', 'error' => $e->getMessage()];
        }
    }



public function storeCardOrder(array $data, DeliveryService $deliveryService): array
{
    DB::beginTransaction();

    try {
        $user = Auth::user();
        if (!$user) {
            return ['success' => false, 'message' => 'User not authenticated!'];
        }

        $name = !empty(trim($data['name'] ?? '')) ? $data['name'] : $user->name;
        $phone = !empty(trim($data['phone'] ?? '')) ? $data['phone'] : $user->phone;
        $address = !empty(trim($data['address'] ?? '')) ? $data['address'] : $user->address;

        if (($data['payment_status'] ?? '') !== 'completed') {
            return ['success' => false, 'message' => 'Payment not completed'];
        }

        $cartItems = CartItem::whereHas('cart', fn($query) => $query->where('user_id', $user->id))
            ->with('medicine:id,name,price,quantity,image')
            ->get();

        if ($cartItems->isEmpty()) {
            return ['success' => false, 'message' => 'Cart is empty!'];
        }

        $distanceKm = $deliveryService->getDistanceFromPharmacy($data['latitude'], $data['longitude']);
        $deliveryFee = $deliveryService->calculateDeliveryFee($distanceKm);
        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);
        $totalPrice = $subtotal + $deliveryFee;

        $order = Order::create([
            'user_id' => $user->id,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'distance' => $distanceKm,
            'delivery_fee' => $deliveryFee,
            'total_price' => $totalPrice,
            'payment_method' => 'card',
            'paymob_order_id' => $data['paymob_order_id'] ?? null,
            'status' => 'confirmed',
            'payment_state' => 'completed',
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'medicine_id' => $item->medicine_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);

            $medicine = Product::find($item->medicine_id);
            if ($medicine) {
                $medicine->quantity -= $item->quantity;
                $medicine->save();

                // إشعار عند انخفاض الكمية
                if ($medicine->quantity <= 3) {
                    $admins = \App\Models\User::where('user_type', 'admin')->get();
                    foreach ($admins as $admin) {
                        FilamentNotification::make()
                            ->title('كمية منخفضة')
                            ->body("المنتج '{$medicine->name}' الكمية المتبقية: {$medicine->quantity}")
                            ->warning()
                            ->actions([
                                Action::make('view')
                                    ->label('عرض المنتج')
                                    ->url(\App\Filament\Resources\Pharmacy\ProductResource::getUrl('edit', ['record' => $medicine]))
                                    ->color('primary'),
                            ])
                            ->sendToDatabase($admin);
                    }
                }
            }
        }

        // حذف عناصر السلة بعد الطلب
        CartItem::whereHas('cart', fn($query) => $query->where('user_id', $user->id))->delete();

        DB::commit();

        return [
            'success' => true,
            'order' => $order,
            'user_info' => compact('name', 'phone', 'address'),
            'distance' => round($distanceKm, 2),
            'delivery_fee' => $deliveryFee,
            'total_price' => $totalPrice,
        ];

    } catch (Exception $e) {
        DB::rollBack();
        return ['success' => false, 'message' => 'Something went wrong!', 'error' => $e->getMessage()];
    }
}


}
