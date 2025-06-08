<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\pharmacy\Cart\AddToCartRequest;
use App\Http\Requests\pharmacy\Cart\UpdateCartItemRequest;
use App\Services\pharmacy\CartService\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function addItem(AddToCartRequest $request): JsonResponse
    {
        $result = $this->cartService->addItem($request->medicine_id, $request->quantity);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'] ?? null,
            ], 400);
        }

        return response()->json([
            'message' => $result['message'],
            'cart_item' => $result['cart_item']
        ], 200);
    }

    public function updateItem(UpdateCartItemRequest $request, int $cartItemId): JsonResponse
    {
        $result = $this->cartService->updateItem($cartItemId, $request->quantity);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'] ?? null,
            ], 400);
        }

        return response()->json([
            'message' => $result['message'],
            'cart_item' => $result['cart_item']
        ], 200);
    }

    public function index(): JsonResponse
    {
        $result = $this->cartService->getCart();

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'] ?? null,
            ], 200);
        }

        return response()->json($result, 200);
    }

    public function removeItem(int $cartItemId): JsonResponse
    {
        $result = $this->cartService->removeItem($cartItemId);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'] ?? null,
            ], 404);
        }

        return response()->json(['message' => $result['message']], 200);
    }

    public function clearCart(): JsonResponse
    {
        $result = $this->cartService->clearCart();

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'] ?? null,
            ], 200);
        }

        return response()->json(['message' => $result['message']], 200);
    }
}
