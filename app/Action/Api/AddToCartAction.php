<?php

namespace App\Action\Api;

use App\Models\Cart;
use App\Models\Meal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddToCartAction
{
    public function execute($user, array $data): Cart
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        $cart = $user->getOrCreateCart();
        $meal = Meal::findOrFail($data['meal_id']);

        if (!$meal->is_available) {
            throw ValidationException::withMessages([
                'meal_id' => ['This meal is currently unavailable'],
            ]);
        }

        if (!$meal->isInStock()) {
            throw ValidationException::withMessages([
                'meal_id' => ['This meal is out of stock'],
            ]);
        }

        if ($meal->stock_quantity < $data['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$meal->stock_quantity} items available in stock"],
            ]);
        }

        DB::beginTransaction();

        try {
            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $data['quantity'];
                $effectiveMax = min($maxPerProduct, $meal->stock_quantity);

                if ($newQuantity > $effectiveMax) {
                    throw ValidationException::withMessages([
                        'quantity' => ["Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}."],
                    ]);
                }

                $cartItem->update([
                    'quantity' => $newQuantity,
                ]);
            } else {
                $discountAmount = 0;
                if ($meal->resolved_discount_price) {
                    $discountAmount = ($meal->price - $meal->resolved_discount_price) * $data['quantity'];
                }

                $cart->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $data['quantity'],
                    'unit_price' => $meal->final_price,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $meal->final_price * $data['quantity'],
                ]);
            }

            $cart->calculateTotals();
            DB::commit();

            return $cart;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}