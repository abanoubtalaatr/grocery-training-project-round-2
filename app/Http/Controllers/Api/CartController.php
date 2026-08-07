<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Carts\AddCartItemAction;
use App\Actions\Api\Carts\ClearCartAction;
use App\Actions\Api\Carts\RemoveCartItemAction;
use App\Actions\Api\Carts\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\Api\CartResource;
use App\Models\CartItem;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CartController extends Controller
{
    public function index(Request $request, ShippingService $shippingService): JsonResponse
    {
        $cart = $request->user()
            ->getOrCreateCart()
            ->load(['items.meal.category', 'items.meal.subcategory']);

        [$shippingFee, $totalWithShipping] = $this->shippingTotals($request, $cart->subtotal, $cart->total, $shippingService);

        return $this->successResponse(
            new CartResource($cart, $shippingFee, $totalWithShipping),
            'Cart retrieved successfully'
        );
    }

    public function addItem(AddCartItemRequest $request, AddCartItemAction $addCartItem): JsonResponse
    {
        try {
            $cart = $addCartItem->execute($request->user(), $request->validated());
        } catch (InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse(
            new CartResource($cart),
            'Item added to cart successfully'
        );
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem, UpdateCartItemAction $updateCartItem): JsonResponse
    {
        $this->authorize('update', $cartItem);

        try {
            $cart = $updateCartItem->execute($cartItem, (int) $request->validated('quantity'));
        } catch (InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse(
            new CartResource($cart),
            'Cart item updated successfully'
        );
    }

    public function removeItem(CartItem $cartItem, RemoveCartItemAction $removeCartItem): JsonResponse
    {
        $this->authorize('delete', $cartItem);

        $cart = $removeCartItem->execute($cartItem);

        return $this->successResponse(
            new CartResource($cart),
            'Item removed from cart successfully'
        );
    }

    public function clear(Request $request, ClearCartAction $clearCart): JsonResponse
    {
        $cart = $clearCart->execute($request->user());

        return $this->successResponse(
            new CartResource($cart),
            'Cart cleared successfully'
        );
    }

    private function shippingTotals(Request $request, mixed $subtotal, mixed $total, ShippingService $shippingService): array
    {
        $deliveryType = $request->query('delivery_type');

        if (!in_array($deliveryType, ['delivery', 'pickup'], true)) {
            return [null, null];
        }

        $shippingFee = $shippingService->calculateShippingFee((float) $subtotal, $deliveryType);

        return [$shippingFee, (float) $total + $shippingFee];
    }
}
