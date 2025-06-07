<?php
namespace App\Services\CartService;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Exception;

class CartService
{
    public function addItem(int $medicineId, int $quantity)
    {
        try {
            $medicine = Product::findOrFail($medicineId);

            if ($medicine->quantity < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Not enough stock available',
                ];
            }

            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('medicine_id', $medicineId)
                ->first();

            if ($existingItem) {
                return [
                    'success' => false,
                    'message' => 'Item already exists in cart.',
                ];
            }

            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'medicine_id' => $medicineId,
                'quantity' => $quantity,
                'price' => $medicine->price,
            ]);

            return [
                'success' => true,
                'cart_item' => $cartItem,
                'message' => 'Item added to cart',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Something went wrong while adding item to cart.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateItem(int $cartItemId, int $quantity)
    {
        try {
            $cartItem = CartItem::findOrFail($cartItemId);

            $medicine = Product::findOrFail($cartItem->medicine_id);

            if ($medicine->quantity < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Not enough stock available',
                ];
            }

            $cartItem->update(['quantity' => $quantity]);

            return [
                'success' => true,
                'cart_item' => $cartItem,
                'message' => 'Cart item updated',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Something went wrong while updating cart item.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getCart()
    {
        try {
            $cart = Cart::where('user_id', Auth::id())->first();

            if (!$cart || $cart->items()->count() === 0) {
                return [
                    'success' => false,
                    'message' => 'Cart is empty',
                ];
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

            return [
                'success' => true,
                'cart_id' => $cart->id,
                'items' => $cartItems,
                'total' => $cartItems->sum('total_price'),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Something went wrong while fetching the cart.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function removeItem(int $cartItemId)
    {
        try {
            $cartItem = CartItem::findOrFail($cartItemId);

            $cartItem->delete();

            return [
                'success' => true,
                'message' => 'Cart item removed',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Something went wrong while removing the cart item.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function clearCart()
    {
        try {
            $cart = Cart::where('user_id', Auth::id())->first();

            if (!$cart || $cart->items()->count() === 0) {
                return [
                    'success' => false,
                    'message' => 'Cart is empty',
                ];
            }

            $cart->items()->delete();

            return [
                'success' => true,
                'message' => 'Cart cleared',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Something went wrong while clearing the cart.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
