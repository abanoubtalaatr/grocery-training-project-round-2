<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Actions\Cart\GetCartAction;
use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\ClearCartAction;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use Exception;

class CartController extends Controller
{
    /**
     * Get user's cart
     */
    public function index(Request $request, GetCartAction $action): JsonResponse
    {
        try {
            $deliveryType = $request->query('delivery_type');
            $result = $action->execute($request->user(), $deliveryType);

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'data' => $this->formatCart($result['cart'], $result['shipping_fee'], $result['total_with_shipping']),
            ]);
        } catch (Exception $e) {
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
    public function addItem(AddToCartRequest $request, AddCartItemAction $action): JsonResponse
    {
        try {
            $validated = $request->validated();
            $cart = $action->execute($request->user(), $validated['meal_id'], $validated['quantity']);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId, UpdateCartItemAction $action): JsonResponse
    {
        try {
            $validated = $request->validated();
            $cart = $action->execute($request->user(), $itemId, $validated['quantity']);

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId, RemoveCartItemAction $action): JsonResponse
    {
        try {
            $cart = $action->execute($request->user(), $itemId);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cart
     */
    public function clear(Request $request, ClearCartAction $action): JsonResponse
    {
        try {
            $cart = $action->execute($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format cart data for response.
     * When shipping fee and total_with_shipping are provided (e.g. from delivery_type query), they are included.
     */
    private function formatCart(Cart $cart, ?float $shippingFee = null, ?float $totalWithShipping = null): array
    {
        $data = [
            'id' => $cart->id,
            'status' => $cart->isEmpty() ? 'empty' : 'not empty',
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'meal' => [
                        'id' => $item->meal->id,
                        'title' => $item->meal->title,
                        'slug' => $item->meal->slug,
                        'image_url' => $item->meal->image_url,
                        ...$item->meal->getApiPriceAttributes(),
                        'rating' => (float) $item->meal->rating,
                        'size' => $item->meal->size,
                        'brand' => $item->meal->brand,
                        'stock_quantity' => $item->meal->stock_quantity,
                        'is_available' => $item->meal->is_available,
                        'in_stock' => $item->meal->isInStock(),
                        'category' => $item->meal->category ? [
                            'id' => $item->meal->category->id,
                            'name' => $item->meal->category->name,
                        ] : null,
                        'subcategory' => $item->meal->subcategory ? [
                            'id' => $item->meal->subcategory->id,
                            'name' => $item->meal->subcategory->name,
                        ] : null,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount_amount' => (float) $item->discount_amount,
                    'subtotal' => (float) $item->subtotal,
                ];
            }),
            'item_count' => $cart->item_count,
            'subtotal' => (float) $cart->subtotal,
            'tax' => (float) $cart->tax,
            'discount' => (float) $cart->discount,
            'total' => (float) $cart->total,
            'is_empty' => $cart->isEmpty(),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ];

        if ($shippingFee !== null && $totalWithShipping !== null) {
            $data['shipping_fee'] = (float) $shippingFee;
            $data['total_with_shipping'] = (float) $totalWithShipping;
        }

        return $data;
    }
}
