<?php

namespace App\Action\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Services\ShippingService;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class CreateOrderAction
{
    public function execute($user, array $validated): Order
    {
        // Get user's active cart
        $cart = $user->activeCart()->with('items.meal')->first();

        if (! $cart || $cart->isEmpty()) {
            throw new \RuntimeException('Your cart is empty. Please add items to your cart before placing an order.');
        }

        $itemsResult = $this->validateAndProcessCartItems($cart->items);
        if (! $itemsResult['success']) {
            throw new \RuntimeException($itemsResult['response']['message']);
        }

        $items = $itemsResult['items'];

        $cart->calculateTotals();
        $shippingService = app(ShippingService::class);
        $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $validated['delivery_type']);
        $totals = [
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'discount' => $cart->discount,
            'shipping_fee' => $shippingFee,
            'total' => (float) $cart->subtotal + (float) $cart->tax + $shippingFee,
        ];

        DB::beginTransaction();

        $stripePaymentIntentId = null;

        // Process payment if needed
        if (($validated['payment_method'] ?? null) === 'card') {
            $paymentResult = $this->processPayment($user, $validated, $totals['total']);
            if (! $paymentResult['success']) {
                DB::rollBack();
                throw new \RuntimeException($paymentResult['response']['message'] ?? 'Payment failed');
            }
            $stripePaymentIntentId = $paymentResult['stripe_payment_intent_id'] ?? null;
        }

        $order = $this->createOrder($user, $validated, $totals['subtotal'], $totals, $stripePaymentIntentId);

        $this->createOrderItems($order, $items);

        $this->clearUserCart($user);

        if (isset($validated['special_note_id'])) {
            OrderNote::create([
                'order_id' => $order->id,
                'special_note_id' => $validated['special_note_id'],
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        if (isset($validated['notes'])) {
            OrderNote::create([
                'order_id' => $order->id,
                'special_note_id' => null,
                'notes' => $validated['notes'],
            ]);
        }

        DB::commit();

        return $order->load(['items.meal', 'address']);
    }

    private function validateAndProcessCartItems($cartItems): array
    {
        $items = [];
        $subtotal = 0;

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (! $meal) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'One or more items in your cart are no longer available.',
                    ],
                ];
            }

            if (! $meal->is_available) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Meal '{$meal->title}' is currently unavailable",
                    ],
                ];
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Only {$meal->stock_quantity} items available for '{$meal->title}'",
                    ],
                ];
            }

            $maxPerProduct = config('cart.max_quantity_per_product', 10);
            if ($cartItem->quantity > $maxPerProduct) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Maximum {$maxPerProduct} units per product allowed. Please reduce quantity for '{$meal->title}'.",
                    ],
                ];
            }

            $items[] = [
                'meal' => $meal,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'discount_amount' => $cartItem->discount_amount,
                'subtotal' => $cartItem->subtotal,
            ];

            $subtotal += $cartItem->subtotal;
        }

        return [
            'success' => true,
            'items' => $items,
            'subtotal' => $subtotal,
        ];
    }

    private function processPayment($user, array $validated, float $total): array
    {
        if (($validated['payment_method'] ?? null) !== 'card') {
            return ['success' => true];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Stripe customer not found. Please add a payment method first.',
                ],
            ];
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($total * 100),
                'currency' => 'usd',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $validated['payment_method_id'],
                'off_session' => true,
                'confirm' => true,
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'Payment failed: ' . $paymentIntent->status,
                    ],
                ];
            }

            return [
                'success' => true,
                'stripe_payment_intent_id' => $paymentIntent->id,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Payment processing failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    private function createOrder($user, array $validated, float $subtotal, array $totals, ?string $stripePaymentIntentId = null): Order
    {
        $isHostedStripe = ($validated['payment_method'] ?? null) === 'stripe_checkout';

        return Order::create([
            'user_id' => $user->id,
            'address_id' => ($validated['delivery_type'] ?? null) === 'delivery' ? ($validated['address_id'] ?? null) : null,
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_method_id' => null,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'delivery_type' => $validated['delivery_type'] ?? null,
            'status' => $isHostedStripe ? 'awaiting_payment' : 'placed',
            'subtotal' => $subtotal,
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'shipping_fee' => $totals['shipping_fee'],
            'total' => $totals['total'],
            'notes' => $validated['notes'] ?? null,
            'placed_at' => $isHostedStripe ? null : now(),
        ]);
    }

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'meal_id' => $item['meal']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'subtotal' => $item['subtotal'],
            ]);

            $item['meal']->decrement('stock_quantity', $item['quantity']);
        }
    }

    private function clearUserCart($user): void
    {
        $cart = $user->activeCart()->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['status' => 'completed']);
        }
    }
}
