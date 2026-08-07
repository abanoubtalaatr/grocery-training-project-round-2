<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    public function execute(User $user, string $itemId, int $quantity): Cart
    {
        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);
        $meal = $cartItem->meal;

        if ($meal->stock_quantity < $quantity) {
            throw new Exception("Only {$meal->stock_quantity} items available in stock");
        }

        return DB::transaction(function () use ($cart, $cartItem, $quantity) {
            $cartItem->update([
                'quantity' => $quantity,
            ]);

            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            return $cart;
        });
    }
}
