<?php

namespace App\Actions\Api\Order;

use App\Models\Order;
use Illuminate\Http\Request;

class ShowOrderAction
{
    public function run(Request $request, Order $order): Order
    {
        abort_if(
            $order->user_id !== $request->user()->id,
            403,
            'Unauthorized.'
        );

        return $order->load([
            'items.meal.category',
            'items.meal.subcategory',
            'address',
        ]);
    }
}