<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\User;

class ClearCartAction
{
    public function execute(User $user): Cart
    {
        $cart = $user->getOrCreateCart();

        $cart->items()->delete();
        $cart->calculateTotals();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $cart;
    }
}
