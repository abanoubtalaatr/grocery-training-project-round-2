<?php

namespace App\Actions\Order;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Services\ShippingService;
use App\Services\Email\Contracts\EmailServiceInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateOrderAction
{
    public function __construct(
        private readonly ShippingService $shippingService,
        private readonly EmailServiceInterface $emailService
    ) {}

    /**
     * Create a new order.
     * 
     * @throws Exception
     */
    public function execute(User $user, array $validated): Order
    {
        $cart = $user->activeCart()->with('items.meal')->first();

        if (!$cart || $cart->isEmpty()) {
            throw new Exception('Your cart is empty. Please add items to your cart before placing an order.');
        }

        // Validate and process items from cart
        $itemsResult = $this->validateAndProcessCartItems($cart->items);
        $items = $itemsResult['items'];

        // Calculate totals and shipping
        $cart->calculateTotals();
        $shippingFee = $this->shippingService->calculateShippingFee((float) $cart->subtotal, $validated['delivery_type']);
        $totals = [
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'discount' => $cart->discount,
            'shipping_fee' => $shippingFee,
            'total' => (float) $cart->subtotal + (float) $cart->tax + $shippingFee,
        ];
        
        DB::beginTransaction();

        try {
            // Note: Stripe processing logic can be abstracted to a separate action if needed
            $stripePaymentIntentId = null;

            // Create order
            $order = $this->createOrder($user, $validated, $totals['subtotal'], $totals, $stripePaymentIntentId);

            // Create order items and update stock
            $this->createOrderItems($order, $items);

            // Clear user's active cart
            $this->clearUserCart($user);
            
            // Notes
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
            $this->emailService->sendOrderConfirmation($order);

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validateAndProcessCartItems($cartItems): array
    {
        $items = [];
        $subtotal = 0;
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (!$meal) {
                throw new Exception('One or more items in your cart are no longer available.');
            }

            if (!$meal->is_available) {
                throw new Exception("Meal '{$meal->title}' is currently unavailable");
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                throw new Exception("Only {$meal->stock_quantity} items available for '{$meal->title}'");
            }

            if ($cartItem->quantity > $maxPerProduct) {
                throw new Exception("Maximum {$maxPerProduct} units per product allowed. Please reduce quantity for '{$meal->title}'.");
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
            'items' => $items,
            'subtotal' => $subtotal,
        ];
    }

    private function createOrder($user, array $validated, float $subtotal, array $totals, ?string $stripePaymentIntentId = null): Order
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
