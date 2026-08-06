<?php

namespace App\Action\Api;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class GetOrderAction
{
    public function execute($user, Order $order): Order
    {
        if ($order->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'order' => ['Order not found'],
            ]);
        }

        $order->load(['items.meal', 'address']);

        return $order;
    }
}
