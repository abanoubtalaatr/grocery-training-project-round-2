<?php

namespace App\Actions\Order;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class GetOrdersAction
{
    /**
     * Get all user orders.
     */
    public function execute(User $user): Collection
    {
        return Order::with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
