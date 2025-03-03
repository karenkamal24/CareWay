<?php

namespace App\Http\Controllers\User;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DeliveryZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\DeliveryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getZones()
    {
        return response()->json(DeliveryZone::all());
    }
    // public function storeCashOrder(Request $request)

    // {
    //     $request->validate([
    //         'delivery_zone_id' => 'nullable|exists:delivery_zones,id',
    //         'name' => 'required|string|max:255',
    //         'phone' => 'required|string|max:20',
    //         'address' => 'required|string|max:500',
    //     ]);
    //     DB::beginTransaction();
    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             return response()->json(['error' => 'User not authenticated!'], 401);
    //         }

    //         $cartItems = CartItem::whereHas('cart', function ($query) use ($user) {
    //             $query->where('user_id', $user->id);
    //         })->get();

    //         if ($cartItems->isEmpty()) {
    //             return response()->json(['error' => 'Cart is empty!'], 400);
    //         }

    //         foreach ($cartItems as $cartItem) {
    //             if (!$cartItem->medicine_id) {
    //                 return response()->json(['error' => 'Something went wrong!', 'details' => 'Missing medicine_id in cart item.'], 400);
    //             }
    //         }
    //         $totalPrice = $cartItems->sum(fn($item) => $item->price * $item->quantity);

    //         $deliveryFee = 0;
    //         $deliveryZone = null;
    //         if ($request->delivery_zone_id) {
    //             $deliveryZone = DeliveryZone::find($request->delivery_zone_id);
    //             $deliveryFee = $deliveryZone?->delivery_fee ?? 0;
    //         }
    //         $finalPrice = $totalPrice + $deliveryFee;
    //         $order = Order::create([
    //             'user_id' => $user->id,
    //             'delivery_zone_id' => $request->delivery_zone_id,
    //             'name' => $request->name,
    //             'phone' => $request->phone,
    //             'address' => $request->address,
    //             'total_price' => $finalPrice,
    //             'payment_method' => 'cash',
    //             'status' => 'pending',
    //         ]);
    //         OrderItem::create([
    //             'order_id' => $order->id,
    //             'medicine_id' => $cartItem->medicine_id, 
    //             'quantity' => $cartItem->quantity,
    //             'price' => $cartItem->price,
    //         ]);
            
    //         CartItem::whereHas('cart', function ($query) use ($user) {
    //             $query->where('user_id', $user->id);
    //         })->delete();
    //         DB::commit();
    //         return response()->json([
    //             'message' => 'Cash order created successfully!',
    //             'order' => [
    //                 'id' => $order->id,
    //                 'user' => [
    //                     'id' => $user->id,
    //                     'name' => $user->name,
    //                     'phone' => $user->phone,
    //                     'address' => $user->address,
    //                 ],
    //                 'delivery_zone' => $deliveryZone ? [
    //                     'id' => $deliveryZone->id,
    //                     'name' => $deliveryZone->name,
    //                     'delivery_fee' => $deliveryFee,
    //                 ] : null,
    //                 'total_price' => $finalPrice,
    //                 'payment_method' => 'cash',
    //                 'status' => 'pending',
    //                 'created_at' => $order->created_at,
    //             ],
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => 'Something went wrong!', 'details' => $e->getMessage()], 500);
    //     }
    // }
    

  /**
     * إنشاء طلب جديد باستخدام الدفع النقدي
     *
     * @param Request $request
     * @param DeliveryService $deliveryService
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCashOrder(Request $request, DeliveryService $deliveryService)
    {
        $request->validate([
            'latitude' => 'required|numeric',  
            'longitude' => 'required|numeric', 
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);
    
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated!'], 401);
            }
    
         
            $name = !empty(trim($request->name)) ? $request->name : $user->name;
            $phone = !empty(trim($request->phone)) ? $request->phone : $user->phone;
            $address = !empty(trim($request->address)) ? $request->address : $user->address;
    
            $cartItems = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();
    
            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Cart is empty!'], 400);
            }
    
            $distanceKm = $deliveryService->getDistanceFromPharmacy($request->latitude, $request->longitude);
            $deliveryFee = $deliveryService->calculateDeliveryFee($distanceKm);
            $totalPrice = $cartItems->sum(fn($item) => $item->price * $item->quantity);
            $finalPrice = $totalPrice + $deliveryFee;
    
            $order = Order::create([
                'user_id' => $user->id,
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'distance' => $distanceKm,
                'delivery_fee' => $deliveryFee,
                'total_price' => $finalPrice,
                'payment_method' => 'cash',
                'status' => 'pending',
            ]);
    
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'medicine_id' => $cartItem->medicine_id, 
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                ]);
            
                $medicine = Product::find($cartItem->medicine_id);
                if ($medicine) {
                    $medicine->quantity -= $cartItem->quantity;
                    $medicine->save();
                }
            }
    
            CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->delete();
    
            DB::commit();
    
            return response()->json([
                'message' => 'Cash order created successfully!',
                'order' => [
                    'id' => $order->id,
                    'user' => [
                        'id' => $user->id,
                        'name' => $name,
                        'phone' => $phone,
                        'address' => $address,
                    ],
                    'distance' => round($distanceKm, 2) . ' km',
                    'delivery_fee' => $deliveryFee . ' EGP',
                    'total_price' => $finalPrice . ' EGP',
                    'payment_method' => 'cash',
                    'status' => 'pending',
                    'latitude' => $order->latitude,   
                    'longitude' => $order->longitude, 
                    'created_at' => $order->created_at,

                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong!', 'details' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        // التحقق من أن المستخدم مسجل دخوله
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated!'], 401);
        }

        $order = Order::with(['user', 'orderItems.medicine'])->find($id);
    
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $orderItems = $order->orderItems->map(function ($item) {
            return [
                'medicine_id' => $item->medicine_id,
                'name' => $item->medicine->name,
                'image' => asset('storage/products/' . $item->medicine->image), 
            ];
        });

        return response()->json([
            'id' => $order->id,
            'name' => $order->name,
            'phone' => $order->phone,
            'address' => $order->address,
            'total_price' => $order->total_price,
            'payment_method' => $order->payment_method,
            'status' => $order->status,
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            'order_items' => $orderItems, 
        ]);
    }

    public function index()
    {
        // التحقق من أن المستخدم مسجل دخوله
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated!'], 401);
        }

        $orders = Order::with(['orderItems.medicine'])->get();

        $ordersData = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'name' => $order->name,
                'phone' => $order->phone,
                'address' => $order->address,
                'total_price' => $order->total_price,
                'payment_method' => $order->payment_method,
                'status' => $order->status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'medicine_id' => $item->medicine_id,
                        'name' => $item->medicine->name, // عرض اسم الدواء
                        'image' => asset('storage/products/' . $item->medicine->image), // رابط الصورة
                    ];
                }),
            ];
        });

        return response()->json($ordersData);
    }

    public function delete($id)
    {
       
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated!'], 401);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delete();
        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
     
}
