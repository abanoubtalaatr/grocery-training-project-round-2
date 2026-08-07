<?php

namespace App\Actions\Api\Order;

use App\Models\Order;
use App\Models\User;

class GetOrderTrackingAction
{
    /**
     * Get the last active order with tracking status.
     */
    public function execute(User $user): array
    {
        $order = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$order) {
            return [
                'success' => false,
                'message' => 'No active order found',
                'status_code' => 404,
            ];
        }

        if ($order->status === 'awaiting_payment') {
            return [
                'success' => true,
                'order' => $order,
                'awaiting_payment' => true,
                'tracking' => null,
            ];
        }

        return [
            'success' => true,
            'order' => $order,
            'tracking' => $this->buildTrackingData($order),
        ];
    }

    /**
     * Build tracking data with status positions.
     */
    private function buildTrackingData(Order $order): array
    {
        return [
            'position' => $order->status_position,
            'status' => $order->status,
            'status_description' => $order->status_description,
            'positions' => [
                [
                    'position' => 1,
                    'status' => 'placed',
                    'label' => 'Order Placed',
                    'description' => 'Your order has been placed',
                    'completed' => in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered']),
                    'timestamp' => $order->placed_at,
                ],
                [
                    'position' => 2,
                    'status' => 'processing',
                    'label' => 'Processing',
                    'description' => 'Your order is being processed',
                    'completed' => in_array($order->status, ['processing', 'shipping', 'out_for_delivery', 'delivered']),
                    'timestamp' => $order->processing_at,
                ],
                [
                    'position' => 3,
                    'status' => 'shipping',
                    'label' => 'Shipping',
                    'description' => 'Your order is being shipped',
                    'completed' => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']),
                    'timestamp' => $order->shipping_at,
                ],
                [
                    'position' => 4,
                    'status' => 'out_for_delivery',
                    'label' => 'Out for Delivery',
                    'description' => 'Your order is on the way',
                    'completed' => in_array($order->status, ['out_for_delivery', 'delivered']),
                    'timestamp' => $order->out_for_delivery_at,
                ],
                [
                    'position' => 5,
                    'status' => 'delivered',
                    'label' => 'Delivered',
                    'description' => 'Your order has been delivered',
                    'completed' => $order->status === 'delivered',
                    'timestamp' => $order->delivered_at,
                ],
            ],
        ];
    }
}
