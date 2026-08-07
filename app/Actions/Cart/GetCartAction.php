<?php

namespace App\Actions\Cart;

use App\Models\User;

class GetCartAction
{
    public function execute(User $user)
    {
        $cart = $user->getOrCreateCart();

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return $cart;
    }
}