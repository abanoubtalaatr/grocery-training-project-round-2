<?php

namespace App\Action\Cart;

use App\Models\Meal;
use Illuminate\Support\Facades\DB;

class AddItemToCartAction
{
    public function handle($user, int $mealId, int $quantity)
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        $meal = Meal::select(['id', 'price', 'resolved_discount_price', 'final_price', 'stock_quantity', 'is_available', 'title', 'slug', 'image_url', 'rating', 'size', 'brand'])
            ->findOrFail($mealId);

        if (!$meal->is_available) {
            return [
                'status' => false,
                'code' => 400,
                'message' => 'This meal is currently unavailable',
            ];
        }

        if (!$meal->isInStock()) {
            return [
                'status' => false,
                'code' => 400,
                'message' => 'This meal is out of stock',
            ];
        }

        if ($meal->stock_quantity < $quantity) {
            return [
                'status' => false,
                'code' => 400,
                'message' => "Only {$meal->stock_quantity} items available in stock",
            ];
        }

        DB::beginTransaction();

        try {
            $cart = $user->getOrCreateCart();

            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                $effectiveMax = min($maxPerProduct, $meal->stock_quantity);
                if ($newQuantity > $effectiveMax) {
                    DB::rollBack();
                    return [
                        'status' => false,
                        'code' => 400,
                        'message' => "Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}.",
                    ];
                }

                if ($meal->stock_quantity < $newQuantity) {
                    DB::rollBack();
                    return [
                        'status' => false,
                        'code' => 400,
                        'message' => "Only {$meal->stock_quantity} items available in stock",
                    ];
                }

                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $discountAmount = 0;
                if ($meal->resolved_discount_price) {
                    $discountAmount = ($meal->price - $meal->resolved_discount_price) * $quantity;
                }

                $cartItem = $cart->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $quantity,
                    'unit_price' => $meal->final_price,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $meal->final_price * $quantity,
                ]);
            }

            $cart->calculateTotals();

            DB::commit();

            return [
                'status' => true,
                'code' => 200,
                'cart' => $cart,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status' => false,
                'code' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }
}
