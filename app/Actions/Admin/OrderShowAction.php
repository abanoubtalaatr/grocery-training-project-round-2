<?php

namespace App\Actions\Admin;

use App\Models\Order;

class OrderShowAction
{
    public function execute(Order $order): Order
    {
        $order->load(['user', 'address', 'items.meal', 'notes.specialNote', 'invoice']);

        return $order;
    }
}
