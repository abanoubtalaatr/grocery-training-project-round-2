<?php

namespace App\Actions\Api\Cart;

use App\Exceptions\Cart\InsufficientStockException;
use App\Models\Cart;
use App\Models\Meal;
use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    public function execute(Cart $cart, string $itemId, int $quantity): Cart
    {
        return DB::transaction(function () use ($cart, $itemId, $quantity) {
            $cartItem = $cart->items()->lockForUpdate()->findOrFail($itemId);
            $meal = Meal::lockForUpdate()->findOrFail($cartItem->meal_id);

            if ($meal->stock_quantity < $quantity) {
                throw new InsufficientStockException($meal->stock_quantity);
            }

            $cartItem->updateQuantity($quantity);

            return $cart->fresh(['items.meal.category', 'items.meal.subcategory']);
        });
    }
}