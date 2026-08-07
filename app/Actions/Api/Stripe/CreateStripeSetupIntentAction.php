<?php

namespace App\Actions\Api\Stripe;

use App\Models\User;
use Stripe\Customer;
use Stripe\SetupIntent;
use Stripe\Stripe;

class CreateStripeSetupIntentAction
{
    /**
     * Execute the action to create a Stripe setup intent.
     */
    public function execute(User $user): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->full_name ?? $user->email,
            ]);
            $user->update(['stripe_customer_id' => $customer->id]);
        }

        $intent = SetupIntent::create([
            'customer' => $user->stripe_customer_id,
            'payment_method_types' => ['card'],
        ]);

        return ['clientSecret' => $intent->client_secret];
    }
}
