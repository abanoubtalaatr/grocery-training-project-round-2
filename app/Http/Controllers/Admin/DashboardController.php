<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'    => User::count(),
            'active_users'   => User::where('is_active', true)->count(),
            'total_meals'    => Meal::count(),
            'total_orders'   => Order::count(),
            'pending_orders' => Order::where('status', 'placed')->count(),
            'total_revenue'  => Order::whereNotIn('status', ['cancelled', 'awaiting_payment'])->sum('total'),
            'total_reviews'  => Review::count(),
            'total_categories' => Category::count(),
        ];

        // Orders by status
        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Monthly revenue (last 6 months)
        $monthlyRevenue = Order::whereNotIn('status', ['cancelled', 'awaiting_payment'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->take(8)
            ->get();

        // Top selling meals
        $topMeals = Meal::withCount('favorites')
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        // New users this month
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Orders this month
        $ordersThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.dashboard', compact(
            'stats',
            'ordersByStatus',
            'monthlyRevenue',
            'recentOrders',
            'topMeals',
            'newUsersThisMonth',
            'ordersThisMonth'
        ));
    }
}
