<?php

namespace App\Actions\Api\Cart;

use App\Models\Cart;

class ClearCartAction
{
    public function execute(Cart $cart): Cart
    {
        $cart->items->each->delete();

        return $cart->fresh();
    }
}