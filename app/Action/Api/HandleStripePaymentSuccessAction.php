<?php

namespace App\Action;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;
use Illuminate\Validation\ValidationException;

class HandleStripePaymentSuccessAction
{
    public function execute(string $sessionId): array
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);
        } catch (Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'session' => ['Unable to verify payment session.'],
            ]);
        }

        if ($session->payment_status !== 'paid') {
            throw ValidationException::withMessages([
                'payment' => ['Payment has not been completed.'],
            ]);
        }

        $order = $this->resolveOrder($session);

        if (!$order) {
            throw ValidationException::withMessages([
                'order' => ['Order not found.'],
            ]);
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
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'message' => 'Payment successful. Your order has been placed.',
        ];
    }

    private function resolveOrder(Session $session): ?Order
    {
        $orderId = $session->metadata->order_id ?? null;
        if ($orderId) {
            return Order::whereKey((int) $orderId)->first();
        }

        if ($session->client_reference_id) {
            return Order::whereKey((int) $session->client_reference_id)->first();
        }

        return null;
    }
}