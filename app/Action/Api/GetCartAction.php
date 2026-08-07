<?php

namespace App\Action\Api;

use App\Models\Cart;
use App\Services\ShippingService;

class GetCartAction
{
    public function execute($user, ?string $deliveryType = null): array
    {
        $cart = $user->getOrCreateCart();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        $shippingFee = null;
        $totalWithShipping = null;

        if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingService = app(ShippingService::class);
            $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
            $totalWithShipping = (float) $cart->total + $shippingFee;
        }

        return [
            'cart' => $cart,
            'shipping_fee' => $shippingFee,
            'total_with_shipping' => $totalWithShipping,
        ];
    }
}