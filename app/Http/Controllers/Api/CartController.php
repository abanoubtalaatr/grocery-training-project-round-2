<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\Api\CartResource;
use App\Services\ShippingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * Get user's cart
     */
    public function index(Request $request, ShippingService $shippingService): JsonResponse
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        $deliveryType = $request->query('delivery_type');
        if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingFee       = $shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
            $totalWithShipping = (float) $cart->total + $shippingFee;
        } else {
            $shippingFee       = null;
            $totalWithShipping = null;
        }

        return $this->success(
            new CartResource($cart, $shippingFee, $totalWithShipping),
            'Cart retrieved successfully'
        );
    }

    /**
     * Add item to cart
     */
    public function addItem(AddCartItemRequest $request, AddCartItemAction $action): JsonResponse
    {
        $cart = $action->execute(
            $request->user(),
            (int) $request->input('meal_id'),
            (int) $request->input('quantity')
        );

        return $this->success(
            new CartResource($cart),
            'Item added to cart successfully'
        );
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId, UpdateCartItemAction $action): JsonResponse
    {
        $cart = $action->execute(
            $request->user(),
            $itemId,
            (int) $request->input('quantity')
        );

        return $this->success(
            new CartResource($cart),
            'Cart item updated successfully'
        );
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId, RemoveCartItemAction $action): JsonResponse
    {
        $cart = $action->execute($request->user(), $itemId);

        return $this->success(
            new CartResource($cart),
            'Item removed from cart successfully'
        );
    }

    /**
     * Clear cart
     */
    public function clear(Request $request, ClearCartAction $action): JsonResponse
    {
        $cart = $action->execute($request->user());

        return $this->success(
            new CartResource($cart),
            'Cart cleared successfully'
        );
    }
}
