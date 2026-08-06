<?php

namespace App\Action\Api;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function execute($user): Cart
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