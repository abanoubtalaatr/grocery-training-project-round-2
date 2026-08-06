<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddItemToCartAction
{
    public function execute(User $user, array $validated): Cart
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        $cart = $user->getOrCreateCart();
        $meal = Meal::findOrFail($validated['meal_id']);

        if (!$meal->is_available) {
            throw ValidationException::withMessages(['meal_id' => 'This meal is currently unavailable']);
        }

        if (!$meal->isInStock()) {
            throw ValidationException::withMessages(['meal_id' => 'This meal is out of stock']);
        }

        if ($meal->stock_quantity < $validated['quantity']) {
            throw ValidationException::withMessages(['quantity' => "Only {$meal->stock_quantity} items available in stock"]);
        }

        $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            $effectiveMax = min($maxPerProduct, $meal->stock_quantity);
            if ($newQuantity > $effectiveMax) {
                throw ValidationException::withMessages(['quantity' => "Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}."]);
            }
            if ($meal->stock_quantity < $newQuantity) {
                throw ValidationException::withMessages(['quantity' => "Only {$meal->stock_quantity} items available in stock"]);
            }

            $cartItem->update([
                'quantity' => $newQuantity,
            ]);
        } else {
            $discountAmount = 0;
            if ($meal->resolved_discount_price) {
                $discountAmount = ($meal->price - $meal->resolved_discount_price) * $validated['quantity'];
            }

            $cart->items()->create([
                'meal_id' => $meal->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $meal->final_price,
                'discount_amount' => $discountAmount,
                'subtotal' => $meal->final_price * $validated['quantity'],
            ]);
        }

        $cart->calculateTotals();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $cart;
    }
}
