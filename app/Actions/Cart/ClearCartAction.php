<?php

namespace App\Actions\Cart;

use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Exception;

class ClearCartAction
{
    /**
     * Clear all items from the cart.
     * 
     * @throws Exception
     */
    public function execute(User $user): Cart
    {
        $cart = $user->getOrCreateCart();

        DB::beginTransaction();

        try {
            $cart->items()->delete();
            $cart->calculateTotals();

            DB::commit();

            return $cart;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
