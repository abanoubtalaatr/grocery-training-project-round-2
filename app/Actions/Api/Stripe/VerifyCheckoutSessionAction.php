<?php

namespace App\Actions\Api\Stripe;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class VerifyCheckoutSessionAction
{
    public function run(User $user, string $sessionId): array
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);
        } catch (Throwable $e) {
            report($e);
            return [
                'success' => false,
                'status' => 502,
                'message' => 'Unable to verify payment session.',
            ];
        }

        if ($session->payment_status !== 'paid') {
            return [
                'success' => false,
                'status' => 402,
                'message' => 'Payment has not been completed.',
                'data' => ['payment_status' => $session->payment_status],
            ];
        }

        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;
        $order = $orderId
            ? Order::query()->whereKey((int) $orderId)->where('user_id', $user->id)->first()
            : null;

        if (! $order) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Order not found.',
            ];
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

        return [
            'success' => true,
            'message' => 'Payment verified. Order is placed.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
            ],
        ];
    }
}
