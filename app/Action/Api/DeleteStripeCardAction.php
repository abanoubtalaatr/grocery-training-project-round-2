<?php

namespace App\Action\Api;

use Stripe\PaymentMethod;
use Stripe\Stripe;

class DeleteStripeCardAction
{
    public function execute(string $paymentMethodId): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->detach();
    }
}