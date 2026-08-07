<?php

namespace App\Actions\Admin\Order;

use App\Models\Order;

class ShowOrderAction
{
    public function run(Order $order): Order
    {
        return $order->load(['user', 'items.meal', 'address']);
    }
}
