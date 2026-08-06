<?php

namespace App\Actions\Cart;

use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class RemoveCartItemAction
{
    /**
     * Remove an item from the cart.
     * 
     * @throws Exception|ModelNotFoundException
     */
    public function execute(User $user, string $itemId): Cart
    {
        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);

        DB::beginTransaction();

        try {
            $cartItem->delete();

            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            DB::commit();

            return $cart;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
