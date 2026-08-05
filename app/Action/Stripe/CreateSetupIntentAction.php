<?php

namespace App\Action\Stripe;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;

class CreateSetupIntentAction
{
    public function handle($user): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name ?? $user->full_name ?? null,
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
        }

        $intent = SetupIntent::create([
            'customer' => $user->stripe_customer_id,
            'payment_method_types' => ['card'],
        ]);

        return ['client_secret' => $intent->client_secret];
    }
}
