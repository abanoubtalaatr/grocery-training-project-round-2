<?php

namespace App\Action\Api;

use App\Models\Meal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddCartItemAction
{
    public function execute($user, int $mealId, int $quantity)
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        $meal = Meal::findOrFail($mealId);

        if (! $meal->is_available) {
            throw new \RuntimeException('This meal is currently unavailable');
        }

        if (! $meal->isInStock()) {
            throw new \RuntimeException('This meal is out of stock');
        }

        if ($meal->stock_quantity < $quantity) {
            throw new \RuntimeException("Only {$meal->stock_quantity} items available in stock");
        }

        $cart = $user->getOrCreateCart();

        DB::beginTransaction();

        $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            $effectiveMax = min($maxPerProduct, $meal->stock_quantity);
            if ($newQuantity > $effectiveMax) {
                DB::rollBack();
                        throw new \RuntimeException("Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}.");
            }
            if ($meal->stock_quantity < $newQuantity) {
                DB::rollBack();
                throw new \RuntimeException("Only {$meal->stock_quantity} items available in stock");
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
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
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        DB::commit();

        return $cart;
    }
}
