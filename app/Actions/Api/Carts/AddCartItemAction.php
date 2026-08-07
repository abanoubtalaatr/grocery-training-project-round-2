<?php

namespace App\Actions\Api\Carts;

use App\Models\Cart;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AddCartItemAction
{
    public function execute(User $user, array $data): Cart
    {
        return DB::transaction(function () use ($user, $data) {
            $cart = $user->getOrCreateCart();
            $meal = Meal::findOrFail($data['meal_id']);
            $quantity = (int) $data['quantity'];
            $maxPerProduct = config('cart.max_quantity_per_product', 10);

            $this->ensureMealCanBeAdded($meal, $quantity);

            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                $effectiveMax = min($maxPerProduct, $meal->stock_quantity);

                if ($newQuantity > $effectiveMax) {
                    throw new InvalidArgumentException("Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}.");
                }

                $this->ensureMealCanBeAdded($meal, $newQuantity);
                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $discountAmount = $meal->resolved_discount_price
                    ? ($meal->price - $meal->resolved_discount_price) * $quantity
                    : 0;

                $cart->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $quantity,
                    'unit_price' => $meal->final_price,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $meal->final_price * $quantity,
                ]);
            }

            return $cart->fresh()->load(['items.meal.category', 'items.meal.subcategory']);
        });
    }

    private function ensureMealCanBeAdded(Meal $meal, int $quantity): void
    {
        if (!$meal->is_available) {
            throw new InvalidArgumentException('This meal is currently unavailable');
        }

        if (!$meal->isInStock()) {
            throw new InvalidArgumentException('This meal is out of stock');
        }

        if ($meal->stock_quantity < $quantity) {
            throw new InvalidArgumentException("Only {$meal->stock_quantity} items available in stock");
        }
    }
}
