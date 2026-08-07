<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Meal;
use App\Services\ShippingService;
use App\Actions\Cart\GetCartAction;
use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\ClearCartAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct(
        protected GetCartAction $getCartAction,
        protected AddCartItemAction $addCartItemAction,
        protected UpdateCartItemAction $updateCartItemAction,
        protected RemoveCartItemAction $removeCartItemAction,
        protected ClearCartAction $clearCartItemAction,
        
    ){

    } 
    /**
     * Get user's cart
     */
    public function index(Request $request): JsonResponse
    {
        try {

$cart = $this->getCartAction->execute($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'data' => new CartResource($cart),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        try {
            $maxPerProduct = config('cart.max_quantity_per_product', 10);

            $validated = $request->validated();
            $cart = $this->addCartItemAction->execute($request->validated(),$request->user());
            $user = $request->user();
            $cart = $user->getOrCreateCart();
            $meal = Meal::findOrFail($validated['meal_id']);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => new CartResource($cart),
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId): JsonResponse
    {

    try {

        $cart = $this->updateCartItemAction->execute(
            $request->validated(),
            $request->user(),
            $itemId
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => new CartResource($cart),
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

        return response()->json([
            'success' => false,
            'message' => 'Cart item not found',
        ], 404);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }

    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId): JsonResponse
    {
    try {

        $cart = $this->removeCartItemAction->execute(
            $request->user(),
            $itemId
        );

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'data' => new CartResource($cart),
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

        return response()->json([
            'success' => false,
            'message' => 'Cart item not found',
        ], 404);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
    try {

        $cart = $this->clearCartItemAction->execute(
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => new CartResource($cart),
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
    }

    /**
     * Format cart data for response.
     * When shipping fee and total_with_shipping are provided (e.g. from delivery_type query), they are included.
     */

}
