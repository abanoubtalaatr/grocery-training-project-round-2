<?php

namespace App\Action\Api;

use App\Models\Order;

class GetUserOrdersAction
{
    public function execute($user)
    {
        return Order::where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}