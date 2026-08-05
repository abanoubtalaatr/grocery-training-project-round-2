<?php

namespace App\Action\Stripe;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class ChargeSavedCardAction
{
    public function handle($user, string $paymentMethodId, float $amount): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
            return ['status' => false, 'message' => 'No stripe customer configured for user'];
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) round($amount * 100),
                'currency' => config('cashier.currency', 'usd'),
                'customer' => $user->stripe_customer_id,
                'payment_method' => $paymentMethodId,
                'off_session' => true,
                'confirm' => true,
            ]);

            return ['status' => true, 'payment_intent' => $paymentIntent];
        } catch (\Exception $e) {
            report($e);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
