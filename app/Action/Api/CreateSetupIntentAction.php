<?php

namespace App\Action\Api;

use Stripe\Customer;
use Stripe\SetupIntent;
use Stripe\Stripe;

class CreateSetupIntentAction
{
    public function execute($user): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (!$user->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->update(['stripe_customer_id' => $customer->id]);
        }

        $intent = SetupIntent::create([
            'customer' => $user->stripe_customer_id,
            'payment_method_types' => ['card'],
        ]);

        return [
            'client_secret' => $intent->client_secret,
        ];
    }
}