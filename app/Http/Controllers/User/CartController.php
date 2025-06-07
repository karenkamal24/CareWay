<?php

namespace App\Http\Controllers\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
class CartController extends Controller
{
public function addItem(Request $request){


    $request->validate([
        'medicine_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ]);
    $medicine = Product::find($request->medicine_id);
    if ($medicine->quantity < $request->quantity) {
        return response()->json([
            'message' => 'Not enough stock available',
            'available_quantity' => $medicine->quantity
        ], 400);
    }
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('medicine_id', $medicine->id)
            ->first();

            if ($cartItem) {
            return response()->json([
            'message' => 'Item already exists in cart.'
            ], 400);
            }
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'medicine_id' => $medicine->id,
                'quantity' => $request->quantity,
                'price' => $medicine->price,
            ]);

            return response()->json([
                'message' => 'Item added to cart',
                'cart_item' => $cartItem
            ], status: 200);


 }
 public function updateItem(Request $request, $cartItemId)
 {
     $request->validate([
         'quantity' => 'required|integer|min:1',
     ]);

     $cartItem = CartItem::find($cartItemId);
     if (!$cartItem) {
         return response()->json(['message' => 'Cart item not found'], 404);
     }
     $medicine = Product::find($cartItem->medicine_id);

     if ($medicine->quantity < $request->quantity) {
         return response()->json([
             'message' => 'Not enough stock available',
             'available_quantity' => $medicine->quantity
         ], 400);
     }

     $cartItem->update(['quantity' => $request->quantity]);

     return response()->json([
         'message' => 'Cart item updated',
         'cart_item' => $cartItem
     ], 200);
 }
 public function index()
 {
     $cart = Cart::where('user_id', Auth::id())->first();

     if (!$cart) {
         return response()->json(['message' => 'Cart is empty'], 200);
     }

     $cartItems = $cart->items()->with('medicine')->get()->map(function ($item) {
         return [
             'id' => $item->id,
             'medicine_id' => $item->medicine_id,
             'name' => $item->medicine->name,
             'quantity' => $item->quantity,
             'price' => $item->price,
             'total_price' => $item->quantity * $item->price,
             'main_image_url' => $item->medicine->image ? url('storage/' . $item->medicine->image) : null,
         ];
     });

     return response()->json([
         'cart_id' => $cart->id,
         'items' => $cartItems,
         'total' => $cartItems->sum('total_price')
     ], 200);
 }
    public function removeItem($cartItemId)
    {
        $cartItem = CartItem::find($cartItemId);
        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Cart item removed'], 200);
    }
    public function clearCart()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart is empty'], 200);
        }

        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared'], 200);
    }
}
