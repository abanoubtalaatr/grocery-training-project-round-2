<?php

namespace App\Actions\Api\Stripe;

use App\Models\User;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class ChargeSavedCardAction
{
    public function run(User $user, array $data): PaymentIntent
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        return PaymentIntent::create([
            'amount' => (int) ($data['amount'] * 100),
            'currency' => 'usd',
            'customer' => $user->stripe_customer_id,
            'payment_method' => $data['payment_method_id'],
            'off_session' => true,
            'confirm' => true,
        ]);
    }
}
