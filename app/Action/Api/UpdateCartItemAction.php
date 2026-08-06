<?php

namespace App\Action\Api;

use Illuminate\Support\Facades\DB;
use App\Models\Meal;

class UpdateCartItemAction
{
    public function execute($user, string $itemId, int $quantity)
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);
        $meal = $cartItem->meal;

        if ($meal->stock_quantity < $quantity) {
            throw new \RuntimeException("Only {$meal->stock_quantity} items available in stock");
        }

        DB::beginTransaction();

        $cartItem->update(['quantity' => $quantity]);

        $cart->calculateTotals();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        DB::commit();

        return $cart;
    }
}
