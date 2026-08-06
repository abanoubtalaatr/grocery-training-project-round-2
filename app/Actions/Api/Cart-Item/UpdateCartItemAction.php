<?php

namespace App\Actions\Api\CartItem;

use App\Models\CartItem;
use Illuminate\Http\Request;

class UpdateCartItemAction
{
    public function run(Request $request, CartItem $cartItem)
    {
        $meal = $cartItem->meal;

        if ($meal->stock_quantity < $request->quantity) {
            abort(400, "Only {$meal->stock_quantity} items available in stock");
        }

        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        $cart = $cartItem->cart;

        $cart->calculateTotals();

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return $cart;
    }
}