<?php

namespace App\Actions\Cart;

use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function execute($user)
    {
        $cart = $user->getOrCreateCart();

        DB::beginTransaction();

        try {

            $cart->items()->delete();

            $cart->calculateTotals();

            DB::commit();

            return $cart;

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}