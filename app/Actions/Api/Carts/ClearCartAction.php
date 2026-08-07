<?php

namespace App\Actions\Api\Carts;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function execute(User $user): Cart
    {
        return DB::transaction(function () use ($user) {
            $cart = $user->getOrCreateCart();
            $cart->items()->delete();
            $cart->calculateTotals();

            return $cart->fresh()->load(['items.meal.category', 'items.meal.subcategory']);
        });
    }
}
