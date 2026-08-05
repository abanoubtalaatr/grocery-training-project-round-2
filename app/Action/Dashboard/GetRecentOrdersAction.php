<?php

namespace App\Action\Dashboard;

use App\Models\Order;

class GetRecentOrdersAction
{
    public function handle($user, int $limit = 5): array
    {
        $orders = Order::where('user_id', $user->id)
            ->with(['items' => function ($q) { $q->select(['id','order_id','meal_id','quantity','subtotal']); }, 'items.meal' => function ($q) { $q->select(['id','title','image','category_id','subcategory_id']); }, 'items.meal.category' => function ($q) { $q->select(['id','name']); }, 'items.meal.subcategory' => function ($q) { $q->select(['id','name']); }, 'address'])
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
