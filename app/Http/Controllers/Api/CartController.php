<?php

namespace App\Http\Controllers\Api;

use App\Action\Cart\GetCartAction;
use App\Action\Cart\AddItemToCartAction;
use App\Action\Cart\UpdateCartItemAction;
use App\Action\Cart\RemoveCartItemAction;
use App\Action\Cart\ClearCartAction;
use App\Action\Cart\CartPresenter;
use App\Http\Requests\Api\AddCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function index(GetCartAction $getCartAction): JsonResponse
    {
        $user = request()->user();
        $deliveryType = request()->query('delivery_type');

        $data = $getCartAction->handle($user, $deliveryType);

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'data' => $data,
        ]);
    }

    public function addItem(AddCartItemRequest $request, AddItemToCartAction $action, CartPresenter $presenter): JsonResponse
    {
        $user = $request->user();

        $result = $action->handle($user, (int) $request->input('meal_id'), (int) $request->input('quantity'));

        if (!$result['status']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to add item to cart',
            ], $result['code'] ?? 500);
        }

        $cart = $result['cart'];

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => $presenter->present($cart),
        ]);
    }

    public function updateItem(UpdateCartItemRequest $request, string $itemId, UpdateCartItemAction $action, CartPresenter $presenter): JsonResponse
    {
        $user = $request->user();

        $result = $action->handle($user, $itemId, (int) $request->input('quantity'));

        if (!$result['status']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to update cart item',
            ], $result['code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => $presenter->present($result['cart']),
        ]);
    }

    public function removeItem(string $itemId, RemoveCartItemAction $action, CartPresenter $presenter): JsonResponse
    {
        $user = request()->user();

        $result = $action->handle($user, $itemId);

        if (!$result['status']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to remove item from cart',
            ], $result['code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'data' => $presenter->present($result['cart']),
        ]);
    }

    public function clear(ClearCartAction $action, CartPresenter $presenter): JsonResponse
    {
        $user = request()->user();

        $result = $action->handle($user);

        if (!$result['status']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to clear cart',
            ], $result['code'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => $presenter->present($result['cart']),
        ]);
    }
}
