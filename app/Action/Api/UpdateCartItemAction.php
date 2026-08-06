<?php

namespace App\Action\Api;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCartItemAction
{
    public function execute($user, string $itemId, array $data): Cart
    {
        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);
        $meal = $cartItem->meal;

        if ($meal->stock_quantity < $data['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$meal->stock_quantity} items available in stock"],
            ]);
        }

        DB::beginTransaction();

        try {
            $cartItem->update([
                'quantity' => $data['quantity'],
            ]);

            $cart->calculateTotals();
            DB::commit();

            return $cart;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}