<?php

namespace App\Actions\Api\Payment;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetPaymentHistoryAction
{
    public function run(User $user): Collection
    {
        return Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->with(['items.meal.category', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
