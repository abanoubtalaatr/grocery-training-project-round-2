<?php

namespace App\Action\Api;

use App\Services\StripeCheckoutService;
use App\Models\Order;

class CreateStripeCheckoutSessionAction
{
    public function __construct(private StripeCheckoutService $checkoutService) {}

    public function execute(Order $order, $user, float $amount)
    {
        return $this->checkoutService->createSessionForOrder($order, $user, $amount);
    }
}
