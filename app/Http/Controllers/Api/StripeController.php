<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;
use Stripe\PaymentMethod;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    public function createSetupIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

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

        return response()->json(['clientSecret' => $intent->client_secret]);
    }

    public function listCards(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

        if (!$user->stripe_customer_id) return response()->json([]);

        $cards = PaymentMethod::all([
            'customer' => $user->stripe_customer_id,
            'type' => 'card',
        ]);

        return response()->json($cards->data);
    }


    public function chargeSavedCard(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

        $order = $user->orders()->whereKey($request->order_id)->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if (! in_array($order->status, ['awaiting_payment', 'placed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be charged.',
            ], 422);
        }

        $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);

        if ($paymentMethod->customer !== $user->stripe_customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found.',
            ], 404);
        }

        $amount = (float) $order->total;

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) round($amount * 100),
            'currency' => 'usd',
            'customer' => $user->stripe_customer_id,
            'payment_method' => $request->payment_method_id,
            'off_session' => true,
            'confirm' => true,
            'idempotency_key' => 'charge_order_' . $order->id,
        ]);

        $order->update([
            'stripe_payment_intent_id' => $paymentIntent->id,
            'payment_method_id' => $request->payment_method_id,
        ]);

        return response()->json(['status' => 'success', 'payment_intent' => $paymentIntent]);
    }

    public function deleteCard(Request $request, $id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

        if (! $user->stripe_customer_id) {
            return response()->json(['status' => 'deleted']);
        }

        $paymentMethod = PaymentMethod::retrieve($id);

        if ($paymentMethod->customer !== $user->stripe_customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found.',
            ], 404);
        }

        $paymentMethod->detach();

        return response()->json(['status' => 'deleted']);
    }
}
