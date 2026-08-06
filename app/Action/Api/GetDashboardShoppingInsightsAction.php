<?php

namespace App\Action\Api;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class GetDashboardShoppingInsightsAction
{
    public function execute($user): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $monthlySpend = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $ordersThisMonth = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->get();

        $ordersCount = $ordersThisMonth->count();

        $averageDaysBetweenOrders = 0;
        if ($ordersCount > 1) {
            $orderDates = $ordersThisMonth->pluck('created_at')->sort();
            $totalDays = 0;
            $intervals = 0;

            for ($i = 1; $i < $orderDates->count(); $i++) {
                $days = $orderDates[$i]->diffInDays($orderDates[$i - 1]);
                $totalDays += $days;
                $intervals++;
            }

            $averageDaysBetweenOrders = $intervals > 0 ? round($totalDays / $intervals, 1) : 0;
        }

        $totalSavings = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->sum('discount');

        $mealSavings = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled');
        })
            ->with('meal')
            ->get()
            ->sum(function ($item) {
                if ($item->meal && $item->meal->discount_price) {
                    return ($item->meal->price - $item->meal->discount_price) * $item->quantity;
                }
                return 0;
            });

        $totalSavings += $mealSavings;

        $averageOrderValue = 0;
        if ($ordersCount > 0) {
            $averageOrderValue = (float) ($monthlySpend / $ordersCount);
        }

        return [
            'monthly_spend' => (float) $monthlySpend,
            'orders_this_month' => [
                'count' => $ordersCount,
                'average_days_between_orders' => $averageDaysBetweenOrders,
            ],
            'total_savings' => (float) $totalSavings,
            'average_order_value' => round($averageOrderValue, 2),
        ];
    }
}