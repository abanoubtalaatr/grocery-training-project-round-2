<?php

namespace App\Actions\Api\Cart;

use App\Models\Cart;

class RemoveCartItemAction
{
    public function execute(Cart $cart, string $itemId): Cart
    {
        $cartItem = $cart->items()->findOrFail($itemId);
        $cartItem->delete();

        return $cart->fresh(['items.meal.category', 'items.meal.subcategory']);
    }
}