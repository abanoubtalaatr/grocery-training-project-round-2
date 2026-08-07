<?php

namespace App\Actions\Api\Cart;

use App\Exceptions\Cart\CartQuantityLimitExceededException;
use App\Exceptions\Cart\InsufficientStockException;
use App\Exceptions\Cart\MealOutOfStockException;
use App\Exceptions\Cart\MealUnavailableException;
use App\Models\Cart;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddCartItemAction
{
    public function execute(User $user, int $mealId, int $quantity): Cart
    {
        $cart = $user->getOrCreateCart();

        return DB::transaction(function () use ($cart, $mealId, $quantity) {
            $maxPerProduct = Cart::MAX_QUANTITY_PER_PRODUCT;
            $meal = Meal::lockForUpdate()->findOrFail($mealId);

            $this->assertPurchasable($meal, $quantity);

            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();
            $existingQuantity = $cartItem->quantity ?? 0;
            $newQuantity = $existingQuantity + $quantity;

            $effectiveMax = min($maxPerProduct, $meal->stock_quantity);
            if ($newQuantity > $effectiveMax) {
                throw new CartQuantityLimitExceededException($existingQuantity, $effectiveMax, $maxPerProduct);
            }

            if ($meal->stock_quantity < $newQuantity) {
                throw new InsufficientStockException($meal->stock_quantity);
            }

            if ($cartItem) {
                $cartItem->updateQuantity($newQuantity);
            } else {
                $cart->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $quantity,
                ]);
            }

            return $cart->fresh(['items.meal.category', 'items.meal.subcategory']);
        });
    }

    private function assertPurchasable(Meal $meal, int $quantity): void
    {
        if (! $meal->is_available) {
            throw new MealUnavailableException();
        }

        if (! $meal->isInStock()) {
            throw new MealOutOfStockException();
        }

        if ($meal->stock_quantity < $quantity) {
            throw new InsufficientStockException($meal->stock_quantity);
        }
    }
}