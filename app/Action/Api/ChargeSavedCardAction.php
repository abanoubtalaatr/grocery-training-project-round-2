<?php

namespace App\Action\Api;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class ChargeSavedCardAction
{
    public function execute($user, string $paymentMethodId, float $amount)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) ($amount * 100),
            'currency' => 'usd',
            'customer' => $user->stripe_customer_id,
            'payment_method' => $paymentMethodId,
            'off_session' => true,
            'confirm' => true,
        ]);

        return $paymentIntent;
    }
}
