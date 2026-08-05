<?php

namespace App\Action\Cart;

use Illuminate\Support\Facades\DB;
use App\Models\CartItem;

class UpdateCartItemAction
{
    public function handle($user, string $itemId, int $quantity)
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        $cart = $user->getOrCreateCart();

        $cartItem = $cart->items()->with('meal')->findOrFail($itemId);

        $meal = $cartItem->meal;

        if ($meal->stock_quantity < $quantity) {
            return [
                'status' => false,
                'code' => 400,
                'message' => "Only {$meal->stock_quantity} items available in stock",
            ];
        }

        DB::beginTransaction();

        try {
            $cartItem->update(['quantity' => $quantity]);
            $cart->calculateTotals();

            DB::commit();

            return [
                'status' => true,
                'code' => 200,
                'cart' => $cart,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status' => false,
                'code' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }
}
