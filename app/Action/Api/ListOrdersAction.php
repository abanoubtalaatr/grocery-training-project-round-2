<?php

namespace App\Action\Api;

use App\Models\Order;

class ListOrdersAction
{
    public function execute($user)
    {
        return Order::with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
