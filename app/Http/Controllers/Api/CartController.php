<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetCartAction;
use App\Action\Api\AddCartItemAction;
use App\Action\Api\UpdateCartItemAction;
use App\Action\Api\RemoveCartItemAction;
use App\Action\Api\ClearCartAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\Api\CartResource;
use App\Models\Meal;
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
        $user = $request->user();
        $deliveryType = $request->query('delivery_type');

        $result = $action->execute($user, $deliveryType);
        $cart = $result['cart'];

        $resource = (new CartResource($cart))->additional([
            'shipping_fee' => $result['shipping_fee'],
            'total_with_shipping' => $result['total_with_shipping'],
        ]);

        return $this->success($resource, 'Cart retrieved successfully');
    }

    /**
     * Add item to cart
     */
    public function addItem(AddCartItemRequest $request, AddCartItemAction $action): JsonResponse
    {
        $validated = $request->validated();
        try {
            $cart = $action->execute($request->user(), (int) $validated['meal_id'], (int) $validated['quantity']);

            return $this->success(new CartResource($cart), 'Item added to cart successfully');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->error('Failed to add item to cart', 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId, UpdateCartItemAction $action): JsonResponse
    {
        $validated = $request->validated();
        try {
            $cart = $action->execute($request->user(), $itemId, (int) $validated['quantity']);

            return $this->success(new CartResource($cart), 'Cart item updated successfully');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Cart item not found', 404);
        } catch (\Exception $e) {
            return $this->error('Failed to update cart item', 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId, RemoveCartItemAction $action): JsonResponse
    {
        try {
            $cart = $action->execute($request->user(), $itemId);

            return $this->success(new CartResource($cart), 'Item removed from cart successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Cart item not found', 404);
        } catch (\Exception $e) {
            return $this->error('Failed to remove item from cart', 500);
        }
    }

    /**
     * Clear cart
     */
    public function clear(Request $request, ClearCartAction $action): JsonResponse
    {
        try {
            $cart = $action->execute($request->user());

            return $this->success(new CartResource($cart), 'Cart cleared successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to clear cart', 500);
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
