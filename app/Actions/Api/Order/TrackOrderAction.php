<?php

namespace App\Actions\Api\Order;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackOrderAction
{
    public function run(Request $request): ?Order
    {
        return Order::where('user_id', $request->user()->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with([
                'items.meal.category',
                'items.meal.subcategory',
                'address',
            ])
            ->latest()
            ->first();
    }
}