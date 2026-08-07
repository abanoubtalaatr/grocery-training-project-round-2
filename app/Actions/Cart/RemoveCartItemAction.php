<?php

namespace App\Actions\Cart;

use Illuminate\Support\Facades\DB;

class RemoveCartItemAction
{
    public function execute($user, string $itemId)
    {
        $cart = $user->getOrCreateCart();

        $cartItem = $cart->items()->findOrFail($itemId);

        DB::beginTransaction();

        try {

            $cartItem->delete();

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