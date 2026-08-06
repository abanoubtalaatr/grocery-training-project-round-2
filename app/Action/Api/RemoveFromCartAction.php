<?php

namespace App\Action\Api;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class RemoveFromCartAction
{
    public function execute($user, string $itemId): Cart
    {
        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);

        DB::beginTransaction();

        try {
            $cartItem->delete();
            $cart->calculateTotals();
            DB::commit();

            return $cart;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}