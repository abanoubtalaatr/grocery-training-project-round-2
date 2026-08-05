<?php

namespace App\Action\Stripe;

use Stripe\Stripe;
use Stripe\PaymentMethod;

class ListCardsAction
{
    public function handle($user): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
            return ['data' => []];
        }

        $cards = PaymentMethod::all([
            'customer' => $user->stripe_customer_id,
            'type' => 'card',
        ]);

        return ['data' => $cards->data];
    }
}
