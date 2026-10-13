<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Http\Resources\Api\CartResource;
use App\Models\Cart;
use App\Models\SmartList;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AddSmartListToCartAction
{
    /**
     * Add all available items from a smart list into the user's active cart.
     *
     * @return array<string, mixed>
     */
    public function execute(User $user, SmartList $smartList): array
    {
        if ($smartList->user_id !== $user->id) {
            throw new AccessDeniedHttpException('You are not authorized to access this smart list.');
        }

        $smartList->load('meals');
        $cart = $user->getOrCreateCart();
        $maxPerProduct = (int) config('cart.max_quantity_per_product', 10);

        $added = [];
        $skipped = [];

        DB::transaction(function () use ($cart, $smartList, $maxPerProduct, &$added, &$skipped) {
            foreach ($smartList->meals as $meal) {
                if (! $meal->is_available) {
                    $skipped[] = [
                        'meal_id' => $meal->id,
                        'meal_title' => $meal->title,
                        'reason' => 'Meal is currently unavailable',
                    ];

                    continue;
                }

                if (! $meal->isInStock() || $meal->stock_quantity < 1) {
                    $skipped[] = [
                        'meal_id' => $meal->id,
                        'meal_title' => $meal->title,
                        'reason' => 'Meal is out of stock',
                    ];

                    continue;
                }

                $cartItem = $cart->items()->where('meal_id', $meal->id)->first();
                $currentQty = $cartItem ? (int) $cartItem->quantity : 0;
                $effectiveMax = min($maxPerProduct, (int) $meal->stock_quantity);

                if ($currentQty >= $effectiveMax) {
                    $skipped[] = [
                        'meal_id' => $meal->id,
                        'meal_title' => $meal->title,
                        'reason' => "Maximum allowed quantity ({$effectiveMax}) already in cart",
                    ];

                    continue;
                }

                $newQty = $currentQty + 1;

                if ($cartItem) {
                    $discountAmount = 0;
                    if ($meal->resolved_discount_price) {
                        $discountAmount = ($meal->price - $meal->resolved_discount_price) * $newQty;
                    }

                    $cartItem->update([
                        'quantity' => $newQty,
                        'unit_price' => $meal->final_price,
                        'discount_amount' => $discountAmount,
                        'subtotal' => $meal->final_price * $newQty,
                    ]);
                } else {
                    $discountAmount = 0;
                    if ($meal->resolved_discount_price) {
                        $discountAmount = ($meal->price - $meal->resolved_discount_price) * 1;
                    }

                    $cart->items()->create([
                        'meal_id' => $meal->id,
                        'quantity' => 1,
                        'unit_price' => $meal->final_price,
                        'discount_amount' => $discountAmount,
                        'subtotal' => $meal->final_price * 1,
                    ]);
                }

                $added[] = [
                    'meal_id' => $meal->id,
                    'meal_title' => $meal->title,
                    'quantity' => $newQty,
                ];
            }

            $cart->calculateTotals();
        });

        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return [
            'added_count' => count($added),
            'skipped_count' => count($skipped),
            'added_items' => $added,
            'skipped_items' => $skipped,
            'cart' => new CartResource($cart),
        ];
    }
}
