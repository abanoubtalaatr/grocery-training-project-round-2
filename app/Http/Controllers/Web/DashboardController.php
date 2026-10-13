<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MealView;
use App\Models\Order;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $analytics = $this->getAnalytics($user);
        $recentOrders = $this->getRecentOrders($user);
        $topPurchases = $this->getTopPurchases($user);
        $shoppingInsights = $this->getShoppingInsights($user);

        return view('dashboard.index', [
            'user' => $user,
            'analytics' => $analytics,
            'recentOrders' => $recentOrders,
            'topPurchases' => $topPurchases,
            'shoppingInsights' => $shoppingInsights,
        ]);
    }

    private function getAnalytics($user): array
    {
        $totalTimeSpentMinutes = UserActivity::where('user_id', $user->id)
            ->get()
            ->sum(function ($activity) {
                return $activity->session_start->diffInMinutes($activity->last_activity_at);
            });

        $viewedProductsCount = MealView::where('user_id', $user->id)
            ->distinct('meal_id')
            ->count('meal_id');

        $ordersCount = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->count();

        $notificationsCount = $user->notifications()->whereNull('read_at')->count();

        $favoritesCount = $user->favorites()->count();

        return [
            'time_spent_minutes' => (int) $totalTimeSpentMinutes,
            'viewed_products_count' => (int) $viewedProductsCount,
            'orders_count' => (int) $ordersCount,
            'notifications_count' => (int) $notificationsCount,
            'favorites_count' => (int) $favoritesCount,
        ];
    }

    private function getRecentOrders($user): \Illuminate\Support\Collection
    {
        return $user->orders()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_description' => $order->status_description,
                'total' => (float) $order->total,
                'created_at' => $order->created_at,
                'items_count' => $order->items->sum('quantity'),
            ]);
    }

    private function getTopPurchases($user, int $limit = 5): \Illuminate\Support\Collection
    {
        return Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', function ($q) {
                $q->whereHas('meal');
            })
            ->with('items.meal')
            ->get()
            ->flatMap->items
            ->filter(fn ($item) => $item->meal)
            ->groupBy(fn ($item) => $item->meal_id)
            ->map(fn ($items, $mealId) => [
                'meal_id' => $mealId,
                'title' => $items->first()->meal->title,
                'image_path' => $items->first()->meal->image_path,
                'total_quantity' => $items->sum('quantity'),
                'total_spent' => (float) $items->sum('subtotal'),
            ])
            ->sortByDesc('total_quantity')
            ->take($limit)
            ->values();
    }

    private function getShoppingInsights($user): array
    {
        $orders = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'asc')
            ->get();

        $ordersCount = $orders->count();
        $totalSpent = (float) $orders->sum('total');
        $averageOrderValue = $ordersCount > 0 ? $totalSpent / $ordersCount : 0;

        $avgDaysBetween = 0;
        if ($ordersCount > 1) {
            $diffs = $orders->sliding(2)->map(fn ($pair) => $pair->first()->created_at->diffInDays($pair->last()->created_at));
            $avgDaysBetween = round($diffs->avg(), 1);
        }

        return [
            'total_spent' => $totalSpent,
            'orders_count' => $ordersCount,
            'average_order_value' => round($averageOrderValue, 2),
            'average_days_between' => $avgDaysBetween,
        ];
    }
}
