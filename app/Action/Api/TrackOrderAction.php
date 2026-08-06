<?php

namespace App\Action\Api;

use App\Models\Order;

class TrackOrderAction
{
    public function execute($user): ?Order
    {
        return Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
