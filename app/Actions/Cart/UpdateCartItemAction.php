<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Models\Cart;
use Illuminate\Validation\ValidationException;

class UpdateCartItemAction
{
    public function execute(CartItem $cartItem, array $validated): Cart
    {
        $meal = $cartItem->meal;

        if ($meal->stock_quantity < $validated['quantity']) {
            throw ValidationException::withMessages(['quantity' => "Only {$meal->stock_quantity} items available in stock"]);
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
        ]);

        $cart = $cartItem->cart;
        $cart->calculateTotals();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $cart;
    }
}
