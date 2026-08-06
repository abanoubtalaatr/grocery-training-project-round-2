<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\AddToCartAction;
use App\Action\Api\ClearCartAction;
use App\Action\Api\GetCartAction;
use App\Action\Api\RemoveFromCartAction;
use App\Action\Api\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddToCartRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\Api\CartResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetCartAction $action): JsonResponse
    {
        $deliveryType = $request->query('delivery_type');
        $result = $action->execute($request->user(), $deliveryType);

        return $this->success(new CartResource($result['cart'], $result['shipping_fee'], $result['total_with_shipping']),'Cart retrieved successfully');
    }

    public function addItem(AddToCartRequest $request, AddToCartAction $action): JsonResponse
    {
        $cart = $action->execute($request->user(), $request->validated());
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $this->success(new CartResource($cart),'Item added to cart successfully');
    }

    public function updateItem(UpdateCartItemRequest $request, string $itemId, UpdateCartItemAction $action): JsonResponse
    {
        $cart = $action->execute($request->user(), $itemId, $request->validated());
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $this->success(new CartResource($cart),'Cart item updated successfully');
    }

    public function removeItem(Request $request, string $itemId, RemoveFromCartAction $action): JsonResponse
    {
        $cart = $action->execute($request->user(), $itemId);
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $this->success(new CartResource($cart),'Item removed from cart successfully');
    }

    public function clear(Request $request, ClearCartAction $action): JsonResponse
    {
        $cart = $action->execute($request->user());

        return $this->success(new CartResource($cart),'Cart cleared successfully');
    }
}
