<?php

namespace App\Actions\Admin;

use App\Models\Order;

class OrderDeleteAction
{
    public function execute(Order $order): void
    {
        $order->delete();
    }
}
