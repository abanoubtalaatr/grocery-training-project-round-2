<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cart\AddItemToCartAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Cart\GetSingleCartItemAction;
use App\Actions\Cart\RemoveItemFromCartAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddItemRequest;
use App\Http\Requests\Api\UpdateItemRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * Get user's cart
     */
    public function index(Request $request, GetCartAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->query('delivery_type'));
        $this->authorize('view', $result['cart']);
        return $this->Success(new CartResource($result['cart'], $result['shippingFee'], $result['totalWithShipping']), 'Cart retrieved successfully');
    }

    /**
     * Get single cart item
     */
    public function show(Request $request, string $id, GetSingleCartItemAction $action): JsonResponse
    {
        $cartItem = $action->execute($id);
        $this->authorize('view', $cartItem);
        return $this->Success(new CartItemResource($cartItem), 'Cart item retrieved successfully');
    }
    
    /**
     * Add item to cart
     */
    public function store(AddItemRequest $request, AddItemToCartAction $action): JsonResponse
    {
        $cart = $action->execute($request->user(), $request->validated());
        return $this->Success(new CartResource($cart), 'Item added to cart successfully', 201);
    }

    /**
     * Update cart item quantity
     */
    public function update(UpdateItemRequest $request, string $id, UpdateCartItemAction $action): JsonResponse
    {
        $cartItem = CartItem::with('cart')->findOrFail($id);
        $this->authorize('update', $cartItem);
        $cart = $action->execute($cartItem, $request->validated());
        return $this->Success(new CartResource($cart), 'Cart item updated successfully');
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, string $id, RemoveItemFromCartAction $action): JsonResponse
    {
        $cartItem = CartItem::with('cart')->findOrFail($id);
        $this->authorize('delete', $cartItem);
        $cart = $action->execute($cartItem);
        return $this->Success(new CartResource($cart), 'Item removed from cart successfully');
    }
}
