<?php

namespace App\Action\Api;

use Stripe\PaymentIntent;
use Stripe\Stripe;
use Illuminate\Validation\ValidationException;

class ChargeSavedCardAction
{
    public function execute($user, string $paymentMethodId, float $amount): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($amount * 100),
                'currency' => 'usd',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $paymentMethodId,
                'off_session' => true,
                'confirm' => true,
            ]);

            return [
                'status' => 'success',
                'payment_intent' => $paymentIntent,
            ];
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'payment' => ['Payment failed: ' . $e->getMessage()],
            ]);
        }
    }
}