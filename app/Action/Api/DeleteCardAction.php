<?php

namespace App\Action\Api;

use Stripe\Stripe;
use Stripe\PaymentMethod;

class DeleteCardAction
{
    public function execute(string $paymentMethodId)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->detach();

        return true;
    }
}
