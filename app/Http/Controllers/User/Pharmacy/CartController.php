<?php
namespace App\Http\Controllers\User\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\pharmacy\Cart\AddToCartRequest;
use App\Http\Requests\pharmacy\Cart\UpdateCartItemRequest;
use App\Services\Pharmacy\CartService;
use Illuminate\Http\JsonResponse;
use App\Helpers\ApiResponseHelper;


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
            return ApiResponseHelper::error($result['message']);
        }

        return ApiResponseHelper::success('Item added to cart successfully.', $result['cart_item']);
    }

    public function updateItem(UpdateCartItemRequest $request, int $cartItemId): JsonResponse
    {
        $result = $this->cartService->updateItem($cartItemId, $request->quantity);

        if (!$result['success']) {
            return ApiResponseHelper::error($result['message']);
        }

        return ApiResponseHelper::success('Cart item updated successfully.', $result['cart_item']);
    }

    public function index(): JsonResponse
    {
        $result = $this->cartService->getCart();

        if (!$result['success']) {
            return ApiResponseHelper::error($result['message']);
        }

        return ApiResponseHelper::success('Cart retrieved successfully.', $result['data']);
    }


    public function removeItem(int $cartItemId): JsonResponse
    {
        $result = $this->cartService->removeItem($cartItemId);

        if (!$result['success']) {
            return ApiResponseHelper::notFound($result['message']);
        }

        return ApiResponseHelper::success($result['message']);
    }

    public function clearCart(): JsonResponse
    {
        $result = $this->cartService->clearCart();

        if (!$result['success']) {
            return ApiResponseHelper::error($result['message']);
        }

        return ApiResponseHelper::success($result['message']);
    }
}
