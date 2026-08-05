<?php

namespace App\Action\Cart;

use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function handle($user)
    {
        $cart = $user->getOrCreateCart();

        DB::beginTransaction();

        try {
            $cart->items()->delete();
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
