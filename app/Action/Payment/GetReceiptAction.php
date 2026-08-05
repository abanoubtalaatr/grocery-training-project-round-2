<?php

namespace App\Action\Payment;

use App\Models\Order;

class GetReceiptAction
{
    public function handle($user, Order $order): array
    {
        if ($order->user_id !== $user->id) {
            abort(404, 'Order not found');
        }

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'user']);

        $presenter = new PaymentPresenter();

        return $presenter->formatReceipt($order);
    }
}
