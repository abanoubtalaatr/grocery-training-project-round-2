<?php

namespace App\Actions\Admin\Order;

use App\Models\Order;

class UpdateOrderStatusAction
{
    private const VALID_STATUSES = [
        'placed',
        'processing',
        'shipping',
        'out_for_delivery',
        'delivered',
        'cancelled',
    ];

    private const STATUS_TIMESTAMPS = [
        'placed'          => 'placed_at',
        'processing'      => 'processing_at',
        'shipping'        => 'shipping_at',
        'out_for_delivery' => 'out_for_delivery_at',
        'delivered'       => 'delivered_at',
        'cancelled'       => 'cancelled_at',
    ];

    public function run(Order $order, string $status): Order
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid order status: {$status}");
        }

        $timestampField = self::STATUS_TIMESTAMPS[$status] ?? null;

        $updateData = ['status' => $status];
        if ($timestampField && !$order->{$timestampField}) {
            $updateData[$timestampField] = now();
        }

        $order->update($updateData);

        return $order->fresh(['user', 'items', 'address']);
    }
}
