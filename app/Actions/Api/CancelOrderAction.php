<?php

namespace App\Actions\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CancelOrderAction
{
    /**
     * Cancel an active order and restore meal inventory.
     *
     *
     * @throws HttpException
     */
    public function execute(User $user, Order $order, ?string $reason = null): Order
    {
        if ($order->user_id !== $user->id) {
            throw new AccessDeniedHttpException('You are not authorized to cancel this order.');
        }

        if ($order->status === 'cancelled') {
            throw new HttpException(400, 'This order is already cancelled.');
        }

        if ($order->status === 'delivered') {
            throw new HttpException(400, 'Cannot cancel an order that has already been delivered.');
        }

        if (in_array($order->status, ['shipping', 'out_for_delivery'], true)) {
            throw new HttpException(400, 'Cannot cancel an order that is already in transit or out for delivery.');
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->load('items.meal');

            // Restore product stock quantities
            foreach ($order->items as $item) {
                if ($item->meal) {
                    $item->meal->increment('stock_quantity', $item->quantity);
                }
            }

            $updateData = [
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ];

            if ($reason !== null && $reason !== '') {
                $existingNotes = $order->notes ? $order->notes.' | ' : '';
                $updateData['notes'] = $existingNotes.'Cancellation reason: '.$reason;
            }

            $order->update($updateData);

            return $order->fresh(['items.meal.category', 'items.meal.subcategory', 'address']);
        });
    }
}
