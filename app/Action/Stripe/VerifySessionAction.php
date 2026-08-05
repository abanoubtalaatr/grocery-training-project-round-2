<?php

namespace App\Action\Stripe;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class VerifySessionAction
{
    public function handle($user, string $sessionId)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);
        } catch (Throwable $e) {
            report($e);

            return ['status' => false, 'code' => 502, 'message' => 'Unable to verify payment session.'];
        }

        if ($session->payment_status !== 'paid') {
            return ['status' => false, 'code' => 402, 'message' => 'Payment has not been completed.', 'payment_status' => $session->payment_status];
        }

        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;

        $order = $orderId
            ? Order::query()->whereKey((int) $orderId)->where('user_id', $user->id)->first()
            : null;

        if (! $order) {
            return ['status' => false, 'code' => 404, 'message' => 'Order not found.'];
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

        return ['status' => true, 'order' => $order];
    }
}
