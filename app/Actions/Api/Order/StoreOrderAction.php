<?php

namespace App\Actions\Api\Order;

use App\Http\Requests\Api\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Services\ShippingService;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StoreOrderAction
{
    public function __construct(private ShippingService $shippingService)
    {
    }

    /**
     * Execute the action to create an order from cart.
     */
    public function execute(StoreOrderRequest $request): array
    {
        $user = $request->user();
        $validated = $request->validated();

        // Get user's active cart
        $cart = $user->activeCart()->with('items.meal')->first();

        if (!$cart || $cart->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Your cart is empty. Please add items to your cart before placing an order.',
                'status_code' => 400,
            ];
        }

        // Validate and process items from cart
        $itemsResult = $this->validateAndProcessCartItems($cart->items);
        if (!$itemsResult['success']) {
            return $itemsResult;
        }

        $items = $itemsResult['items'];

        // Calculate totals and shipping
        $cart->calculateTotals();
        $shippingFee = $this->shippingService->calculateShippingFee(
            (float) $cart->subtotal,
            $validated['delivery_type']
        );
        $totals = [
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'discount' => $cart->discount,
            'shipping_fee' => $shippingFee,
            'total' => (float) $cart->subtotal + (float) $cart->tax + $shippingFee,
        ];
        $total = $totals['total'];

        try {
            DB::beginTransaction();

            $stripePaymentIntentId = null;

            // Process payment if needed
            if ($validated['payment_method'] !== 'stripe_checkout') {
                $paymentResult = $this->processPayment($user, $validated, $total);
                if (!$paymentResult['success']) {
                    DB::rollBack();
                    return $paymentResult;
                }
                $stripePaymentIntentId = $paymentResult['stripe_payment_intent_id'] ?? null;
            }

            // Create order
            $order = $this->createOrder($user, $validated, $totals, $stripePaymentIntentId);

            // Create order items and update stock
            $this->createOrderItems($order, $items);

            // Clear user's active cart
            $this->clearUserCart($user);

            // Create order notes
            if (isset($validated['special_note_id'])) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => $validated['special_note_id'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            } elseif (isset($validated['notes'])) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => null,
                    'notes' => $validated['notes'],
                ]);
            }

            DB::commit();

            $order->load(['items.meal', 'address']);

            return [
                'success' => true,
                'order' => $order,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ];
        }
    }

    /**
     * Validate and process order items from cart.
     */
    private function validateAndProcessCartItems($cartItems): array
    {
        $items = [];

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (!$meal) {
                return [
                    'success' => false,
                    'message' => 'One or more items in your cart are no longer available.',
                    'status_code' => 400,
                ];
            }

            if (!$meal->is_available) {
                return [
                    'success' => false,
                    'message' => "Meal '{$meal->title}' is currently unavailable",
                    'status_code' => 400,
                ];
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                return [
                    'success' => false,
                    'message' => "Only {$meal->stock_quantity} items available for '{$meal->title}'",
                    'status_code' => 400,
                ];
            }

            $maxPerProduct = config('cart.max_quantity_per_product', 10);
            if ($cartItem->quantity > $maxPerProduct) {
                return [
                    'success' => false,
                    'message' => "Maximum {$maxPerProduct} units per product allowed. Please reduce quantity for '{$meal->title}'.",
                    'status_code' => 400,
                ];
            }

            $items[] = [
                'meal' => $meal,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'discount_amount' => $cartItem->discount_amount,
                'subtotal' => $cartItem->subtotal,
            ];
        }

        return [
            'success' => true,
            'items' => $items,
        ];
    }

    /**
     * Process payment for card orders.
     */
    private function processPayment($user, array $validated, float $total): array
    {
        if ($validated['payment_method'] !== 'card') {
            return ['success' => true];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        if (!$user->stripe_customer_id) {
            return [
                'success' => false,
                'message' => 'Stripe customer not found. Please add a payment method first.',
                'status_code' => 400,
            ];
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($total * 100),
                'currency' => 'usd',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $validated['payment_method_id'],
                'off_session' => true,
                'confirm' => true,
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'message' => 'Payment failed: ' . $paymentIntent->status,
                    'status_code' => 400,
                ];
            }

            return [
                'success' => true,
                'stripe_payment_intent_id' => $paymentIntent->id,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
                'status_code' => 400,
            ];
        }
    }

    /**
     * Create order record.
     */
    private function createOrder($user, array $validated, array $totals, ?string $stripePaymentIntentId = null): Order
    {
        $isHostedStripe = $validated['payment_method'] === 'stripe_checkout';

        return Order::create([
            'user_id' => $user->id,
            'address_id' => $validated['delivery_type'] === 'delivery' ? $validated['address_id'] : null,
            'payment_method' => $validated['payment_method'],
            'payment_method_id' => null,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'delivery_type' => $validated['delivery_type'],
            'status' => $isHostedStripe ? 'awaiting_payment' : 'placed',
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'shipping_fee' => $totals['shipping_fee'],
            'total' => $totals['total'],
            'notes' => $validated['notes'] ?? null,
            'placed_at' => $isHostedStripe ? null : now(),
            'schedule_delivery' => $validated['schedule_delivery'] ?? null,
            'delivery_speed' => $validated['delivery_speed'] ?? null,
            'estimated_delivery_time' => $validated['estimated_delivery_time'] ?? null,
        ]);
    }

    /**
     * Create order items and update stock.
     */
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

    /**
     * Clear user's active cart.
     */
    private function clearUserCart($user): void
    {
        $cart = $user->activeCart()->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['status' => 'completed']);
        }
    }
}
