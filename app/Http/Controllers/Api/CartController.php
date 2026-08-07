<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Cart\AddCartItemAction;
use App\Actions\Api\Cart\ClearCartAction;
use App\Actions\Api\Cart\RemoveCartItemAction;
use App\Actions\Api\Cart\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\Api\CartResource;
use App\Models\Cart;
use App\Services\ShippingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * Get user's cart.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();
        $this->authorize('view', $cart);

        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        [$shippingFee, $totalWithShipping] = $this->resolveShipping($request, $cart);

        return $this->success(
            new CartResource($cart, $shippingFee, $totalWithShipping),
            'Cart retrieved successfully'
        );
    }

    /**
     * Add item to cart.
     */
    public function addItem(StoreCartItemRequest $request, AddCartItemAction $action): JsonResponse
    {
        $cart = $action->execute(
            $request->user(),
            $request->integer('meal_id'),
            $request->integer('quantity'),
        );

        return $this->success(
            new CartResource($cart),
            'Item added to cart successfully'
        );
    }

    /**
     * Update cart item quantity.
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId, UpdateCartItemAction $action): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();
        $this->authorize('update', $cart);

        $cart = $action->execute($cart, $itemId, $request->integer('quantity'));

        return $this->success(
            new CartResource($cart),
            'Cart item updated successfully'
        );
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(Request $request, string $itemId, RemoveCartItemAction $action): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();
        $this->authorize('update', $cart);

        $cart = $action->execute($cart, $itemId);

        return $this->success(
            new CartResource($cart),
            'Item removed from cart successfully'
        );
    }

    /**
     * Clear cart.
     */
    public function clear(Request $request, ClearCartAction $action): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();
        $this->authorize('update', $cart);

        $cart = $action->execute($cart);

        return $this->success(
            new CartResource($cart),
            'Cart cleared successfully'
        );
    }

    /**
     * Resolve shipping fee and total with shipping.
     */
    private function resolveShipping(Request $request, Cart $cart): array
    {
        $deliveryType = $request->query('delivery_type');

        if (! in_array($deliveryType, ['delivery', 'pickup'], true)) {
            return [null, null];
        }

        $shippingFee = app(ShippingService::class)
            ->calculateShippingFee((float) $cart->subtotal, $deliveryType);

        return [$shippingFee, (float) $cart->total + $shippingFee];
    }
}
