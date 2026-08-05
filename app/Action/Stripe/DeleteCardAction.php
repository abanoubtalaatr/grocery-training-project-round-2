<?php

namespace App\Action\Stripe;

use Stripe\Stripe;
use Stripe\PaymentMethod;

class DeleteCardAction
{
    public function handle(string $paymentMethodId): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->detach();

        return ['status' => true];
    }
}
