<?php

namespace App\Actions\Api\CartItem;

use App\Models\CartItem;

class DeleteCartItemAction
{
    public function run(CartItem $cartItem)
    {
        $cart = $cartItem->cart;

        $cartItem->delete();

        $cart->calculateTotals();

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return $cart;
    }
}