<?php

namespace App\Action\Payment;

use App\Models\Order;

class GetPaymentHistoryAction
{
    public function handle($user)
    {
        $orders = Order::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->with(['items', 'items.meal:id,title,image_url', 'address:id,full_address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => $order->payment_method,
                'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
                'amount' => (float) $order->total,
                'subtotal' => (float) $order->subtotal,
                'tax' => (float) $order->tax,
                'discount' => (float) $order->discount,
                'status' => $order->status,
                'status_description' => $order->status_description,
                'payment_date' => $order->placed_at ?? $order->created_at,
                'created_at' => $order->created_at,
                'items_count' => $order->items->sum('quantity'),
            ];
        });
    }
}
