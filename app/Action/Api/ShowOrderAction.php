<?php

namespace App\Action\Api;

use App\Models\Order;

class ShowOrderAction
{
    public function execute(Order $order): Order
    {
        return $order->load(['items.meal', 'address']);
    }
}
