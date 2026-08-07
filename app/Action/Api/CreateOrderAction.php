<?php

namespace App\Action\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Services\ShippingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function execute($user, array $validated): Order
    {
        $cart = $user->activeCart()->with('items.meal')->first();

        if (!$cart || $cart->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Your cart is empty. Please add items to your cart before placing an order.'],
            ]);
        }

        $items = $this->validateCartItems($cart->items);

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

        try {
            $isHostedStripe = $validated['payment_method'] === 'stripe_checkout';

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $validated['delivery_type'] === 'delivery' ? $validated['address_id'] : null,
                'payment_method' => $validated['payment_method'],
                'payment_method_id' => null,
                'stripe_payment_intent_id' => null,
                'delivery_type' => $validated['delivery_type'],
                'status' => $isHostedStripe ? 'awaiting_payment' : 'placed',
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'discount' => $totals['discount'],
                'shipping_fee' => $totals['shipping_fee'],
                'total' => $totals['total'],
                'notes' => $validated['notes'] ?? null,
                'placed_at' => $isHostedStripe ? null : now(),
            ]);

            $this->createOrderItems($order, $items);

            $this->createOrderNotes($order, $validated);

            $this->clearUserCart($user);

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validateCartItems($cartItems): array
    {
        $items = [];
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (!$meal) {
                throw ValidationException::withMessages([
                    'cart' => ['One or more items in your cart are no longer available.'],
                ]);
            }

            if (!$meal->is_available) {
                throw ValidationException::withMessages([
                    'cart' => ["Meal '{$meal->title}' is currently unavailable"],
                ]);
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                throw ValidationException::withMessages([
                    'cart' => ["Only {$meal->stock_quantity} items available for '{$meal->title}'"],
                ]);
            }

            if ($cartItem->quantity > $maxPerProduct) {
                throw ValidationException::withMessages([
                    'cart' => ["Maximum {$maxPerProduct} units per product allowed. Please reduce quantity for '{$meal->title}'."],
                ]);
            }

            $items[] = [
                'meal' => $meal,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'discount_amount' => $cartItem->discount_amount,
                'subtotal' => $cartItem->subtotal,
            ];
        }

        return $items;
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

    private function createOrderNotes(Order $order, array $validated): void
    {
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