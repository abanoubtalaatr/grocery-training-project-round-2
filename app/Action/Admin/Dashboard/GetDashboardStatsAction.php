<?php

namespace App\Action\Admin\Dashboard;

use App\Models\ContactMessage;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;

class GetDashboardStatsAction
{
    public function execute(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Users stats
        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', $thisMonth)->count();
        $newUsersLastMonth = User::whereBetween('created_at', [$lastMonth, $thisMonth])->count();
        $userGrowth = $this->calculateGrowth($newUsersThisMonth, $newUsersLastMonth);

        // Orders stats
        $totalOrders = Order::count();
        $ordersThisMonth = Order::where('created_at', '>=', $thisMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$lastMonth, $thisMonth])->count();
        $orderGrowth = $this->calculateGrowth($ordersThisMonth, $ordersLastMonth);

        // Revenue stats
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
        $revenueThisMonth = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $thisMonth)
            ->sum('total');
        $revenueLastMonth = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->sum('total');
        $revenueGrowth = $this->calculateGrowth($revenueThisMonth, $revenueLastMonth);

        // Products stats
        $totalProducts = Meal::count();
        $activeProducts = Meal::where('is_available', true)->count();
        $outOfStock = Meal::where('stock_quantity', 0)->count();

        // Pending reviews
        $pendingReviews = Review::where('is_approved', false)->count();

        // New contact messages
        $newMessages = ContactMessage::where('status', 'new')->count();

        return [
            'users' => [
                'total' => $totalUsers,
                'new_this_month' => $newUsersThisMonth,
                'growth_percentage' => $userGrowth,
            ],
            'orders' => [
                'total' => $totalOrders,
                'this_month' => $ordersThisMonth,
                'growth_percentage' => $orderGrowth,
            ],
            'revenue' => [
                'total' => round($totalRevenue, 2),
                'this_month' => round($revenueThisMonth, 2),
                'growth_percentage' => $revenueGrowth,
            ],
            'products' => [
                'total' => $totalProducts,
                'active' => $activeProducts,
                'out_of_stock' => $outOfStock,
            ],
            'pending_items' => [
                'reviews' => $pendingReviews,
                'messages' => $newMessages,
            ],
        ];
    }

    private function calculateGrowth(int|float $current, int|float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
