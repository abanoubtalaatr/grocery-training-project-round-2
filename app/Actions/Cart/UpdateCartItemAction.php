<?php

namespace App\Actions\Cart;

use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class UpdateCartItemAction
{
    /**
     * Update a cart item's quantity.
     * 
     * @throws Exception|ModelNotFoundException
     */
    public function execute(User $user, string $itemId, int $quantity): Cart
    {
        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);
        $meal = $cartItem->meal;

        // Check stock quantity
        if ($meal->stock_quantity < $quantity) {
            throw new Exception("Only {$meal->stock_quantity} items available in stock");
        }

        DB::beginTransaction();

        try {
            $cartItem->update([
                'quantity' => $quantity,
            ]);

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
