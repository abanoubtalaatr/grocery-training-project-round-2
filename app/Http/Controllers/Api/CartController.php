<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Models\Cart;
use App\Models\Meal;
use App\Models\Offer;
use App\Services\ShippingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Get user's cart
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cart = $user->getOrCreateCart();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);
            $deliveryType = $request->query('delivery_type');
            if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
                $shippingService = app(ShippingService::class);
                $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
                $totalWithShipping = (float) $cart->total + $shippingFee;
            } else {
                $shippingFee = null;
                $totalWithShipping = null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'data' => $this->formatCart($cart, $shippingFee, $totalWithShipping),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cart', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        try {
            $maxPerProduct = config('cart.max_quantity_per_product', 10);
            $validated = $request->validate([
                'meal_id' => ['required', 'exists:meals,id'],
                'quantity' => ['required', 'integer', 'min:1', 'max:'.$maxPerProduct],
            ], [
                'quantity.max' => "Maximum {$maxPerProduct} units per product allowed.",
            ]);

            $user = $request->user();
            $cart = $user->getOrCreateCart();
            $meal = Meal::findOrFail($validated['meal_id']);

            // Check if meal is available
            if (! $meal->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => 'This meal is currently unavailable',
                ], 400);
            }

            // Check if meal is in stock
            if (! $meal->isInStock()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This meal is out of stock',
                ], 400);
            }

            // Check if meal has expired
            // if ($meal->isExpired()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'This meal has expired',
            //     ], 400);
            // }

            // Check stock quantity
            if ($meal->stock_quantity < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$meal->stock_quantity} items available in stock",
                ], 400);
            }

            DB::beginTransaction();

            // Check if item already exists in cart
            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

            if ($cartItem) {
                // Update quantity (enforce max per product per user)
                $newQuantity = $cartItem->quantity + $validated['quantity'];
                $effectiveMax = min($maxPerProduct, $meal->stock_quantity);
                if ($newQuantity > $effectiveMax) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => "Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}.",
                    ], 400);
                }
                if ($meal->stock_quantity < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Only {$meal->stock_quantity} items available in stock",
                    ], 400);
                }

                $cartItem->update([
                    'quantity' => $newQuantity,
                ]);
            } else {
                // Create new cart item
                $discountAmount = 0;
                if ($meal->resolved_discount_price) {
                    $discountAmount = ($meal->price - $meal->resolved_discount_price) * $validated['quantity'];
                }

                $cartItem = $cart->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $validated['quantity'],
                    'unit_price' => $meal->final_price,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $meal->final_price * $validated['quantity'],
                ]);
            }

            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add item to cart', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, string $itemId): JsonResponse
    {
        try {
            $maxPerProduct = config('cart.max_quantity_per_product', 10);
            $validated = $request->validate([
                'quantity' => ['required', 'integer', 'min:1', 'max:'.$maxPerProduct],
            ], [
                'quantity.max' => "Maximum {$maxPerProduct} units per product allowed.",
            ]);

            $user = $request->user();
            $cart = $user->getOrCreateCart();

            $cartItem = $cart->items()->findOrFail($itemId);
            $meal = $cartItem->meal;

            // Check stock quantity
            if ($meal->stock_quantity < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$meal->stock_quantity} items available in stock",
                ], 400);
            }

            DB::beginTransaction();

            $cartItem->update([
                'quantity' => $validated['quantity'],
            ]);

            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update cart item', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId): JsonResponse
    {
        try {
            $user = $request->user();
            $cart = $user->getOrCreateCart();

            $cartItem = $cart->items()->findOrFail($itemId);

            DB::beginTransaction();

            $cartItem->delete();

            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            DB::commit();

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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove item from cart', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cart = $user->getOrCreateCart();

            DB::beginTransaction();

            $cart->items()->delete();
            $cart->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to clear cart', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Apply coupon code to cart.
     */
    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cart = $user->getOrCreateCart();
            $cart->load('items');

            if ($cart->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot apply coupon to an empty cart.',
                ], 400);
            }

            $code = trim((string) $request->input('code'));
            $offer = Offer::where('code', $code)->first();

            if (! $offer || ! $offer->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired coupon code.',
                ], 400);
            }

            $subtotal = (float) $cart->items->sum('subtotal');
            if (! $offer->canApplyToAmount($subtotal)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum purchase of $'.number_format((float) $offer->minimum_purchase, 2).' required for this coupon.',
                ], 400);
            }

            $cart->coupon_code = $offer->code;
            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to apply coupon', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Remove coupon code from cart.
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cart = $user->getOrCreateCart();

            $cart->coupon_code = null;
            $cart->coupon_discount = 0;
            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove coupon', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove coupon',
                'error' => 'Internal server error',
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
                        'title' => $item->meal->localized_title,
                        'title_ar' => $item->meal->title_ar,
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
                            'name' => $item->meal->category->localized_name,
                            'name_ar' => $item->meal->category->name_ar,
                        ] : null,
                        'subcategory' => $item->meal->subcategory ? [
                            'id' => $item->meal->subcategory->id,
                            'name' => $item->meal->subcategory->localized_name,
                            'name_ar' => $item->meal->subcategory->name_ar,
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
            'coupon_code' => $cart->coupon_code,
            'coupon_discount' => (float) ($cart->coupon_discount ?? 0),
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
