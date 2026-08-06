<?php

namespace App\Actions\Cart;

use App\Models\User;
use App\Models\Cart;
use App\Models\Meal;
use Illuminate\Support\Facades\DB;
use Exception;

class AddCartItemAction
{
    /**
     * Add an item to the user's cart.
     * 
     * @throws Exception
     */
    public function execute(User $user, int $mealId, int $quantity): Cart
    {
        $cart = $user->getOrCreateCart();
        $meal = Meal::findOrFail($mealId);

        // Check if meal is available
        if (!$meal->is_available) {
            throw new Exception('This meal is currently unavailable');
        }

        // Check if meal is in stock
        if (!$meal->isInStock()) {
            throw new Exception('This meal is out of stock');
        }

        // Check stock quantity
        if ($meal->stock_quantity < $quantity) {
            throw new Exception("Only {$meal->stock_quantity} items available in stock");
        }

        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        DB::beginTransaction();

        try {
            // Check if item already exists in cart
            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

            if ($cartItem) {
                // Update quantity (enforce max per product per user)
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
                // Create new cart item
                $discountAmount = 0;
                if ($meal->resolved_discount_price) {
                    $discountAmount = ($meal->price - $meal->resolved_discount_price) * $quantity;
                }

                $cart->items()->create([
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
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
