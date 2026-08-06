<?php

namespace App\Actions\Api\Stripe;

use App\Models\User;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class ListCardsAction
{
    public function run(User $user): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
            return [];
        }

        $cards = PaymentMethod::all([
            'customer' => $user->stripe_customer_id,
            'type' => 'card',
        ]);

        return $cards->data;
    }
}
