<?php

namespace App\Action\Cart;

use App\Models\Cart;
use App\Services\ShippingService;

class GetCartAction
{
    public function handle($user, ?string $deliveryType = null): array
    {
        $cart = $user->getOrCreateCart();

        $shippingFee = null;
        $totalWithShipping = null;

        if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingService = app(ShippingService::class);
            $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
            $totalWithShipping = (float) $cart->total + $shippingFee;
        }

        $presenter = new CartPresenter();

        return $presenter->present($cart, $shippingFee, $totalWithShipping);
    }
}
