<?php

namespace App\Action\Api;

use App\Models\Order;

class GetPaymentHistoryAction
{
    public function execute($user): array
    {
        $orders = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->with(['items.meal.category', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'orders' => $orders,
            'total_count' => $orders->count(),
            'total_amount' => (float) $orders->sum('total'),
        ];
    }
}