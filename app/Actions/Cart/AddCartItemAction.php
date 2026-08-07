<?php

namespace App\Actions\Cart;

use App\Models\Meal;
use Illuminate\Support\Facades\DB;

class AddCartItemAction
{
    public function execute(array $data, $user)
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        $cart = $user->getOrCreateCart();

        $meal = Meal::findOrFail($data['meal_id']);

        if (!$meal->is_available) {
            throw new \Exception('This meal is currently unavailable');
        }

        if (!$meal->isInStock()) {
            throw new \Exception('This meal is out of stock');
        }

        if ($meal->stock_quantity < $data['quantity']) {
            throw new \Exception("Only {$meal->stock_quantity} items available in stock");
        }

        DB::beginTransaction();

        try {

            $cartItem = $cart->items()
                ->where('meal_id', $meal->id)
                ->first();

            if ($cartItem) {

                $newQuantity = $cartItem->quantity + $data['quantity'];

                $effectiveMax = min($maxPerProduct, $meal->stock_quantity);

                if ($newQuantity > $effectiveMax) {
                    throw new \Exception(
                        "Maximum {$maxPerProduct} units per product allowed."
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
                        * $data['quantity'];
                }

                $cartItem = $cart->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $data['quantity'],
                    'unit_price' => $meal->final_price,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $meal->final_price * $data['quantity'],
                ]);
            }

            $cart->calculateTotals();

            $cart->load([
                'items.meal.category',
                'items.meal.subcategory'
            ]);

            DB::commit();

            return $cart;

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
