<?php

namespace App\Action\Dashboard;

use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Carbon;

class GetOverviewAction
{
    public function handle($user): array
    {
        // Active order
        $activeOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->select(['id', 'order_number', 'status', 'status_description', 'status_position', 'created_at'])
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

        // Current cart
        $cart = $user->activeCart()->with('items')->first();
        $cartData = ['items_count' => 0, 'total' => 0, 'last_updated' => null];
        if ($cart) {
            if (method_exists($cart, 'calculateTotals')) {
                $cart->calculateTotals();
            }
            $cartData = [
                'items_count' => $cart->items->sum('quantity'),
                'total' => (float) $cart->total,
                'last_updated' => $cart->updated_at,
            ];
        }

        // Upcoming delivery
        $upcomingDelivery = Order::where('user_id', $user->id)
            ->whereIn('status', ['placed', 'processing', 'shipping', 'out_for_delivery'])
            ->whereNotNull('estimated_delivery_time')
            ->select(['id', 'order_number', 'estimated_delivery_time'])
            ->orderBy('estimated_delivery_time', 'asc')
            ->first();

        $upcomingDeliveryData = null;
        if ($upcomingDelivery) {
            $dt = $upcomingDelivery->estimated_delivery_time;
            $upcomingDeliveryData = [
                'order_id' => $upcomingDelivery->id,
                'order_number' => $upcomingDelivery->order_number,
                'date' => $dt?->format('Y-m-d'),
                'time' => $dt?->format('H:i'),
                'estimated_delivery_time' => $dt,
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
