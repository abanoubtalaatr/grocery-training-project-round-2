<?php

namespace App\Action\Api;

use Stripe\Stripe;
use Stripe\PaymentMethod;

class ListStripeCardsAction
{
    public function execute($user): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
            return [];
        }

        $cards = PaymentMethod::all([
            'customer' => $user->stripe_customer_id,
            'type' => 'card',
        ]);

        return $cards->data ?? [];
    }
}
