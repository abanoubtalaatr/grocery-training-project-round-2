<?php

namespace App\Actions\Order;

use App\Models\User;
use App\Models\Order;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TrackOrderAction
{
    /**
     * Track the user's latest active order.
     */
    public function execute(User $user): ?Order
    {
        return Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
