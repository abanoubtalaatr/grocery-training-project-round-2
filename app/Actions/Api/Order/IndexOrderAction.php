<?php

namespace App\Actions\Api\Order;

use App\Models\Order;
use Illuminate\Http\Request;

class IndexOrderAction
{
    public function run(Request $request)
    {
        return Order::where('user_id', $request->user()->id)
            ->with([
                'items.meal.category',
                'items.meal.subcategory',
                'address',
            ])
            ->latest()
            ->get();
    }
}