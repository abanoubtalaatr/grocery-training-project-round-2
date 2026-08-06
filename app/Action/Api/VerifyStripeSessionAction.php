<?php

namespace App\Action\Api;

use App\Models\Order;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Facades\DB;

class VerifyStripeSessionAction
{
    public function execute($user, string $sessionId): ?Order
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            return null;
        }

        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;
        $order = $orderId
            ? Order::query()->whereKey((int) $orderId)->where('user_id', $user->id)->first()
            : null;

        if (! $order) {
            return null;
        }

        if ($order->status === 'awaiting_payment') {
            $pi = $session->payment_intent;
            $paymentIntentId = is_string($pi) ? $pi : ($pi->id ?? null);

            DB::transaction(function () use ($order, $paymentIntentId, $session) {
                $order->refresh();
                if ($order->status !== 'awaiting_payment') {
                    return;
                }

                $order->update([
                    'status' => 'placed',
                    'placed_at' => now(),
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'stripe_checkout_session_id' => $session->id,
                ]);
            });

            $order->refresh();
        }

        return $order;
    }
}
