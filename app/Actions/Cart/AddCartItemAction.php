<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\Meal;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class AddCartItemAction
{
    public function execute(User $user, int $mealId, int $quantity): Cart
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        $cart = $user->getOrCreateCart();
        $meal = Meal::findOrFail($mealId);

        if (! $meal->is_available) {
            throw new Exception('This meal is currently unavailable');
        }

        if (! $meal->isInStock()) {
            throw new Exception('This meal is out of stock');
        }

        if ($meal->stock_quantity < $quantity) {
            throw new Exception("Only {$meal->stock_quantity} items available in stock");
        }

        return DB::transaction(function () use ($cart, $meal, $quantity, $maxPerProduct) {
            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                $effectiveMax = min($maxPerProduct, $meal->stock_quantity);

                if ($newQuantity > $effectiveMax) {
                    throw new Exception("Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}.");
                }

                if ($meal->stock_quantity < $newQuantity) {
                    throw new Exception("Only {$meal->stock_quantity} items available in stock");
                }

                $cartItem->update([
                    'quantity' => $newQuantity,
                ]);
            } else {
                $discountAmount = 0;
                if ($meal->resolved_discount_price) {
                    $discountAmount = ($meal->price - $meal->resolved_discount_price) * $quantity;
                }

                $cart->items()->create([
                    'meal_id'         => $meal->id,
                    'quantity'        => $quantity,
                    'unit_price'      => $meal->final_price,
                    'discount_amount' => $discountAmount,
                    'subtotal'        => $meal->final_price * $quantity,
                ]);
            }

            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            return $cart;
        });
    }
}
