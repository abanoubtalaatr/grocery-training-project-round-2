<?php

namespace App\Actions\Api\Stripe;

use Stripe\PaymentMethod;
use Stripe\Stripe;

class DeleteCardAction
{
    public function run(string $id): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = PaymentMethod::retrieve($id);
        $paymentMethod->detach();
    }
}
