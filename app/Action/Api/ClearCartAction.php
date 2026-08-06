<?php

namespace App\Action\Api;

use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function execute($user)
    {
        $cart = $user->getOrCreateCart();

        DB::beginTransaction();

        $cart->items()->delete();
        $cart->calculateTotals();

        DB::commit();

        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $cart;
    }
}
