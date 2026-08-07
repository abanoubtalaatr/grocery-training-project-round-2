<?php

namespace App\Actions\Cart;

use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    public function execute(array $data, $user, string $itemId)
    {
        $cart = $user->getOrCreateCart();

        $cartItem = $cart->items()->findOrFail($itemId);
        $meal = $cartItem->meal;

        if ($meal->stock_quantity < $data['quantity']) {
            throw new \Exception(
                "Only {$meal->stock_quantity} items available in stock"
            );
        }

        DB::beginTransaction();

        try {

            $cartItem->update([
                'quantity' => $data['quantity'],
            ]);

            $cart->calculateTotals();

            $cart->load([
                'items.meal.category',
                'items.meal.subcategory',
            ]);

            DB::commit();

            return $cart;

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}