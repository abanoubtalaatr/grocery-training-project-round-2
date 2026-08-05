<?php

namespace App\Action\Dashboard;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GetShoppingInsightsAction
{
    public function handle($user): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Monthly spend and orders this month (counts)
        $monthlySpend = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $ordersThisMonthDates = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at')
            ->pluck('created_at');

        $ordersCount = $ordersThisMonthDates->count();

        // Average days between orders
        $averageDaysBetweenOrders = 0;
        if ($ordersCount > 1) {
            $totalDays = 0;
            $intervals = 0;
            for ($i = 1; $i < $ordersThisMonthDates->count(); $i++) {
                $days = $ordersThisMonthDates[$i]->diffInDays($ordersThisMonthDates[$i - 1]);
                $totalDays += $days;
                $intervals++;
            }
            $averageDaysBetweenOrders = $intervals > 0 ? round($totalDays / $intervals, 1) : 0;
        }

        // Total savings from order discounts
        $totalSavings = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->sum('discount');

        // Savings from meal discount prices (use DB to compute efficiently)
        $mealSavings = (float) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('meals', 'order_items.meal_id', '=', 'meals.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', '!=', 'cancelled')
            ->select(DB::raw('COALESCE(SUM((meals.price - COALESCE(meals.discount_price,0)) * order_items.quantity),0) as meal_savings'))
            ->value('meal_savings');

        $totalSavings += $mealSavings;

        // Average order value
        $averageOrderValue = $ordersCount > 0 ? (float) ($monthlySpend / $ordersCount) : 0;

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
