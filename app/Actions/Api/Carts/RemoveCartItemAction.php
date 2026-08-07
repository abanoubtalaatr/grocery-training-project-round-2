<?php

namespace App\Actions\Api\Carts;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class RemoveCartItemAction
{
    public function execute(CartItem $cartItem): Cart
    {
        return DB::transaction(function () use ($cartItem) {
            $cart = $cartItem->cart;
            $cartItem->delete();

            return $cart->fresh()->load(['items.meal.category', 'items.meal.subcategory']);
        });
    }
}
