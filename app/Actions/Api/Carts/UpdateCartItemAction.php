<?php

namespace App\Actions\Api\Carts;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateCartItemAction
{
    public function execute(CartItem $cartItem, int $quantity): Cart
    {
        return DB::transaction(function () use ($cartItem, $quantity) {
            $cartItem->loadMissing('meal');

            if ($cartItem->meal->stock_quantity < $quantity) {
                throw new InvalidArgumentException("Only {$cartItem->meal->stock_quantity} items available in stock");
            }

            $cartItem->update(['quantity' => $quantity]);

            return $cartItem->cart->fresh()->load(['items.meal.category', 'items.meal.subcategory']);
        });
    }
}
