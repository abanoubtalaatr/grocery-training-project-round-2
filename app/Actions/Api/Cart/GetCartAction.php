<?php

namespace App\Actions\Api\Cart;

use App\Models\Cart;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class GetCartAction
{
    public function run(Request $request): Cart
    {
        $user = $request->user();

        $cart = $user->getOrCreateCart();

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        $deliveryType = $request->query('delivery_type');

        if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingService = app(ShippingService::class);

            $cart->shipping_fee = $shippingService->calculateShippingFee(
                (float) $cart->subtotal,
                $deliveryType
            );

            $cart->total_with_shipping =
                (float) $cart->total + $cart->shipping_fee;
        }

        return $cart;
    }
}