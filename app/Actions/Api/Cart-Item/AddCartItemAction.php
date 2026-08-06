<?php

namespace App\Actions\Api\CartItem;

use App\Models\Meal;
use Illuminate\Http\Request;

class AddCartItemAction
{
    public function run(Request $request)
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        $user = $request->user();
        $cart = $user->getOrCreateCart();

        $meal = Meal::findOrFail($request->meal_id);

        if (! $meal->is_available) {
            abort(400, 'This meal is currently unavailable');
        }

        if (! $meal->isInStock()) {
            abort(400, 'This meal is out of stock');
        }

        if ($meal->stock_quantity < $request->quantity) {
            abort(400, "Only {$meal->stock_quantity} items available in stock");
        }

        $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

        if ($cartItem) {

            $newQuantity = $cartItem->quantity + $request->quantity;

            $effectiveMax = min($maxPerProduct, $meal->stock_quantity);

            if ($newQuantity > $effectiveMax) {
                abort(
                    400,
                    "Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}."
                );
            }

            $cartItem->update([
                'quantity' => $newQuantity,
            ]);

        } else {

            $discountAmount = 0;

            if ($meal->resolved_discount_price) {
                $discountAmount =
                    ($meal->price - $meal->resolved_discount_price)
                    * $request->quantity;
            }

            $cart->items()->create([
                'meal_id' => $meal->id,
                'quantity' => $request->quantity,
                'unit_price' => $meal->final_price,
                'discount_amount' => $discountAmount,
                'subtotal' => $meal->final_price * $request->quantity,
            ]);
        }

        $cart->calculateTotals();

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return $cart;
    }
}