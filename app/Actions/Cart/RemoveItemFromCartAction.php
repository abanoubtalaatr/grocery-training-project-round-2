<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Models\Cart;

class RemoveItemFromCartAction
{
    public function execute(CartItem $cartItem): Cart
    {
        $cart = $cartItem->cart;
        
        $cartItem->delete();

        $cart->calculateTotals();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $cart;
    }
}
