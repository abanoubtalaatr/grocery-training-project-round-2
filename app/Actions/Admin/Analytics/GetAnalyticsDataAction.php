<?php

namespace App\Actions\Admin\Analytics;

use App\Models\Meal;
use App\Models\Order;
use App\Models\User;

class GetAnalyticsDataAction
{
    public function run(): array
    {
        // Revenue by month (last 12 months)
        $revenueByMonth = Order::where('status', 'delivered')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Orders by status
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Top 10 selling products
        $topProducts = Meal::withCount(['favorites'])
            ->orderByDesc('sold_count')
            ->take(10)
            ->get();

        // New users per month (last 6 months)
        $newUsersByMonth = User::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Payment method breakdown
        $paymentBreakdown = Order::selectRaw('payment_method, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->get();

        // Delivery type breakdown
        $deliveryBreakdown = Order::selectRaw('delivery_type, COUNT(*) as count')
            ->groupBy('delivery_type')
            ->get();

        // Category performance
        $categoryStats = \App\Models\Category::withCount('meals')
            ->withSum('meals', 'sold_count')
            ->orderByDesc('meals_sum_sold_count')
            ->take(10)
            ->get();

        return compact(
            'revenueByMonth',
            'ordersByStatus',
            'topProducts',
            'newUsersByMonth',
            'paymentBreakdown',
            'deliveryBreakdown',
            'categoryStats'
        );
    }
}
