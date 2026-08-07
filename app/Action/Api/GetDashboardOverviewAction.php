<?php

namespace App\Action\Api;

use App\Models\Order;

class GetDashboardOverviewAction
{
    public function execute($user): array
    {
        $activeOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal', 'address'])
            ->orderBy('created_at', 'desc')
            ->first();

        $trackingOrder = null;
        if ($activeOrder) {
            $trackingOrder = [
                'id' => $activeOrder->id,
                'order_number' => $activeOrder->order_number,
                'status' => $activeOrder->status,
                'status_description' => $activeOrder->status_description,
                'status_position' => $activeOrder->status_position,
            ];
        }

        $cart = $user->activeCart()->with('items')->first();
        $cartData = null;
        if ($cart) {
            $cart->calculateTotals();
            $cartData = [
                'items_count' => $cart->items->sum('quantity'),
                'total' => (float) $cart->total,
                'last_updated' => $cart->updated_at,
            ];
        } else {
            $cartData = [
                'items_count' => 0,
                'total' => 0,
                'last_updated' => null,
            ];
        }

        $upcomingDelivery = Order::where('user_id', $user->id)
            ->whereIn('status', ['placed', 'processing', 'shipping', 'out_for_delivery'])
            ->whereNotNull('estimated_delivery_time')
            ->orderBy('estimated_delivery_time', 'asc')
            ->first();

        $upcomingDeliveryData = null;
        if ($upcomingDelivery) {
            $upcomingDeliveryData = [
                'order_id' => $upcomingDelivery->id,
                'order_number' => $upcomingDelivery->order_number,
                'date' => $upcomingDelivery->estimated_delivery_time?->format('Y-m-d'),
                'time' => $upcomingDelivery->estimated_delivery_time?->format('H:i'),
                'estimated_delivery_time' => $upcomingDelivery->estimated_delivery_time,
            ];
        }

        return [
            'tracking_order' => $trackingOrder,
            'loyalty_points' => (int) ($user->loyalty_points ?? 0),
            'store_credits' => (float) ($user->store_credits ?? 0),
            'current_cart' => $cartData,
            'upcoming_delivery' => $upcomingDeliveryData,
        ];
    }
}