<?php

namespace App\Actions\Api\Stripe;

use App\Models\User;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class ChargeSavedCardAction
{
    /**
     * Execute the action to charge a saved card.
     */
    public function execute(User $user, string $paymentMethodId, float $amount): array
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

        return ['status' => 'success', 'payment_intent' => $paymentIntent];
    }
}
