<?php

namespace App\Actions\Api\Payment;

use App\Models\Order;
use App\Models\User;
use RuntimeException;

class GetPaymentReceiptAction
{
    public function run(User $user, Order $order): Order
    {
        if ($order->user_id !== $user->id) {
            throw new RuntimeException('Order not found', 404);
        }

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'user']);

        return $order;
    }
}
