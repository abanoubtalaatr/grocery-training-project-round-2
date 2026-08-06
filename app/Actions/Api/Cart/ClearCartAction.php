<?php

namespace App\Actions\Api\Cart;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function run(Cart $cart): Cart
    {
            $cart->items()->delete();
            $cart->calculateTotals();
            $cart->load([
                'items.meal.category',
                'items.meal.subcategory',
            ]);
        return $cart;
    }
}