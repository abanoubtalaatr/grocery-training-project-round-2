<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\DeliverySlaService;

class OrderObserver
{
    public function saving(Order $order): void
    {
        app(DeliverySlaService::class)->prepareForSave($order);
    }
}
