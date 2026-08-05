<?php

namespace App\Action\Cart;

use Illuminate\Support\Facades\DB;

class RemoveCartItemAction
{
    public function handle($user, string $itemId)
    {
        $cart = $user->getOrCreateCart();

        $cartItem = $cart->items()->findOrFail($itemId);

        DB::beginTransaction();

        try {
            $cartItem->delete();
            $cart->calculateTotals();

            DB::commit();

            return [
                'status' => true,
                'code' => 200,
                'cart' => $cart,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status' => false,
                'code' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }
}
