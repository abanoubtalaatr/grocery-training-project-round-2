<?php

namespace App\Actions\Cart;

use App\Models\CartItem;

class GetSingleCartItemAction
{
    public function execute(string $itemId): CartItem
    {
        return CartItem::with(['cart', 'meal.category', 'meal.subcategory'])->findOrFail($itemId);
    }
}
