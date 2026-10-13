<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class StripePaymentCallbackController extends Controller
{
    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('home')->with('error', 'Missing session ID.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('home')->with('error', 'Unable to verify payment session.');
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('home')->with('error', 'Payment has not been completed.');
        }

        $order = $this->resolveOrder($session);

        if (! $order) {
            return redirect()->route('home')->with('error', 'Order not found.');
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

        return redirect()->route('payment.invoice', $order->id)
            ->with('success', 'Payment successful! Your order has been placed.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $orderId = $request->query('order_id');

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order && $order->status === 'awaiting_payment') {
                DB::transaction(function () use ($order) {
                    $order->refresh();
                    if ($order->status !== 'awaiting_payment') {
                        return;
                    }

                    foreach ($order->items as $item) {
                        if ($item->meal) {
                            $item->meal->increment('stock_quantity', $item->quantity);
                        }
                    }

                    $order->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'notes' => trim(($order->notes ?? '') . "\nPayment cancelled by user."),
                    ]);
                });
            }
        }

        return redirect()->route('cart.show')
            ->with('error', 'Payment was cancelled. Your cart items have been restored.');
    }

    private function resolveOrder(Session $session): ?Order
    {
        $orderId = $session->metadata->order_id ?? null;
        if ($orderId) {
            return Order::query()->whereKey((int) $orderId)->first();
        }

        if ($session->client_reference_id) {
            return Order::query()->whereKey((int) $session->client_reference_id)->first();
        }

        return null;
    }
}
