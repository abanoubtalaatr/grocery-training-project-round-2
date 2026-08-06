<?php

namespace App\Actions\Api\Order;

use App\Http\Requests\StoreOrderRequest;
use App\Jobs\SendInvoiceJob;
use App\Models\Order;
use App\Models\OrderNote;
use App\Services\ShippingService;

class StoreOrderAction
{
    public function run(StoreOrderRequest $request): Order
    {
        $user = $request->user();
        $data = $request->validated();

        $cart = $user->activeCart()
            ->with('items.meal')
            ->firstOrFail();

        abort_if($cart->isEmpty(), 400, 'Your cart is empty.');

        $maxQuantity = config('cart.max_quantity_per_product', 10);

        foreach ($cart->items as $item) {
            $meal = $item->meal;

            abort_if(!$meal, 400, 'One or more items are no longer available.');
            abort_if(!$meal->is_available, 400, "Meal '{$meal->title}' is currently unavailable.");
            abort_if(
                $meal->stock_quantity < $item->quantity,
                400,
                "Only {$meal->stock_quantity} items available for '{$meal->title}'."
            );
            abort_if(
                $item->quantity > $maxQuantity,
                400,
                "Maximum {$maxQuantity} units allowed for '{$meal->title}'."
            );
        }

        $cart->calculateTotals();

        $shippingFee = app(ShippingService::class)
            ->calculateShippingFee((float) $cart->subtotal, $data['delivery_type']);

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $data['delivery_type'] === 'delivery'
                ? $data['address_id']
                : null,

            'payment_method' => $data['payment_method'],
            'payment_method_id' => null,
            'stripe_payment_intent_id' => null,

            'delivery_type' => $data['delivery_type'],
            'status' => 'placed',

            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'discount' => $cart->discount,
            'shipping_fee' => $shippingFee,
            'total' => $cart->subtotal + $cart->tax + $shippingFee,

            'notes' => $data['notes'] ?? null,
            'placed_at' => now(),

            'estimated_delivery_time' => $data['estimated_delivery_time'] ?? null,
            'schedule_delivery' => $data['schedule_delivery'] ?? null,
            'delivery_speed' => $data['delivery_speed'] ?? null,
        ]);

        $order->items()->createMany(
            $cart->items->map(fn($item) => [
                'meal_id' => $item->meal_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'subtotal' => $item->subtotal,
            ])->toArray()
        );

        $cart->items->each(function ($item) {
            $item->meal->decrement('stock_quantity', $item->quantity);
        });

        if (!empty($data['special_note_id']) || !empty($data['notes'])) {
            OrderNote::create([
                'order_id' => $order->id,
                'special_note_id' => $data['special_note_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        }

        $cart->items()->delete();

        $cart->update([
            'status' => 'completed',
        ]);

        SendInvoiceJob::dispatch($order->id)
            ->onQueue('invoices');

        return $order->load([
            'items.meal.category',
            'items.meal.subcategory',
            'address',
        ]);
    }
}