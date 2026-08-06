<?php

namespace App\Action\Api;

use App\Models\Order;

class GetDashboardRecentOrdersAction
{
    public function execute($user, int $limit = 5): array
    {
        $orders = Order::where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_description' => $order->status_description,
                'total' => (float) $order->total,
                'created_at' => $order->created_at,
                'items_count' => $order->items->sum('quantity'),
            ];
        })->toArray();
    }
}