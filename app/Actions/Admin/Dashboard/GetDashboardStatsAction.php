<?php

namespace App\Actions\Admin\Dashboard;

use App\Models\User;
use App\Models\Meal;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Order;
use Carbon\Carbon;

class GetDashboardStatsAction
{
    public function run(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // High level counts
        $totalUsers = User::count();
        $totalMeals = Meal::count();
        $totalCategories = Category::count();
        $totalSubcategories = Subcategory::count();
        $totalOrders = Order::count();

        // Order Status counts
        $pendingOrders = Order::where('status', 'placed')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $shippingOrders = Order::where('status', 'shipping')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        // Revenues
        $totalRevenue = (float) Order::where('status', '!=', 'cancelled')->sum('total');
        $todayRevenue = (float) Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $today)
            ->sum('total');
        $monthlyRevenue = (float) Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total');

        // Stock stats
        $lowStockProducts = Meal::where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', 10)
            ->count();
        $outOfStockProducts = Meal::where('stock_quantity', '<=', 0)->count();

        // Recent items
        $recentOrders = Order::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $recentUsers = User::latest()
            ->limit(5)
            ->get();

        // Top selling products
        $topSellingMeals = Meal::orderBy('sold_count', 'desc')
            ->limit(5)
            ->get();

        return compact(
            'totalUsers',
            'totalMeals',
            'totalCategories',
            'totalSubcategories',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'shippingOrders',
            'deliveredOrders',
            'cancelledOrders',
            'totalRevenue',
            'todayRevenue',
            'monthlyRevenue',
            'lowStockProducts',
            'outOfStockProducts',
            'recentOrders',
            'recentUsers',
            'topSellingMeals'
        );
    }
}
