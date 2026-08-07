<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get all main dashboard analytics and overview data.
     */
    public function getDashboardData(User $user, int $recentLimit = 5, int $topPurchasesLimit = 10): array
    {
        return [
            'overview' => $this->getOverview($user),
            'shopping_insights' => $this->getShoppingInsights($user),
            'category_distribution' => $this->getCategoryDistribution($user),
            'recent_orders' => $this->getRecentOrders($user, $recentLimit),
            'top_purchases' => $this->getTopPurchases($user, $topPurchasesLimit),
        ];
    }

    /**
     * Create a new meal.
     */
    public function createMeal(array $data): Meal
    {
        return Meal::create($data);
    }

    /**
     * Update an existing meal.
     */
    public function updateMeal(Meal $meal, array $data): Meal
    {
        $meal->update($data);
        return $meal->fresh();
    }

    /**
     * Delete a meal.
     */
    public function deleteMeal(Meal $meal): bool
    {
        return $meal->delete();
    }

    /* -------------------------------------------------------------------------- */
    /*                             Private Analytics Methods                      */
    /* -------------------------------------------------------------------------- */

    private function getOverview(User $user): array
    {
        $activeOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal', 'address'])
            ->latest('created_at')
            ->first();

        $cart = $user->activeCart()->with('items')->first();
        if ($cart) {
            $cart->calculateTotals();
        }

        $upcomingDelivery = Order::where('user_id', $user->id)
            ->whereIn('status', ['placed', 'processing', 'shipping', 'out_for_delivery'])
            ->whereNotNull('estimated_delivery_time')
            ->orderBy('estimated_delivery_time', 'asc')
            ->first();

        return [
            'tracking_order' => $activeOrder ? [
                'id' => $activeOrder->id,
                'order_number' => $activeOrder->order_number,
                'status' => $activeOrder->status,
                'status_description' => $activeOrder->status_description,
                'status_position' => $activeOrder->status_position,
            ] : null,
            'loyalty_points' => (int) ($user->loyalty_points ?? 0),
            'store_credits' => (float) ($user->store_credits ?? 0),
            'current_cart' => [
                'items_count' => $cart ? $cart->items->sum('quantity') : 0,
                'total' => $cart ? (float) $cart->total : 0.0,
                'last_updated' => $cart?->updated_at,
            ],
            'upcoming_delivery' => $upcomingDelivery ? [
                'order_id' => $upcomingDelivery->id,
                'order_number' => $upcomingDelivery->order_number,
                'date' => $upcomingDelivery->estimated_delivery_time?->format('Y-m-d'),
                'time' => $upcomingDelivery->estimated_delivery_time?->format('H:i'),
                'estimated_delivery_time' => $upcomingDelivery->estimated_delivery_time,
            ] : null,
        ];
    }

    private function getShoppingInsights(User $user): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $ordersQuery = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled');

        $monthlySpend = (float) (clone $ordersQuery)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        $ordersThisMonth = (clone $ordersQuery)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $ordersCount = $ordersThisMonth->count();

        $averageDaysBetweenOrders = 0;
        if ($ordersCount > 1) {
            $orderDates = $ordersThisMonth->pluck('created_at')->sort()->values();
            $totalDays = 0;
            for ($i = 1; $i < $orderDates->count(); $i++) {
                $totalDays += $orderDates[$i]->diffInDays($orderDates[$i - 1]);
            }
            $averageDaysBetweenOrders = round($totalDays / ($orderDates->count() - 1), 1);
        }

        $totalSavings = (float) (clone $ordersQuery)->sum('discount');

        $mealSavings = OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id)->where('status', '!=', 'cancelled'))
            ->with('meal')
            ->get()
            ->sum(fn($item) => $item->meal?->discount_price ? ($item->meal->price - $item->meal->discount_price) * $item->quantity : 0);

        return [
            'monthly_spend' => $monthlySpend,
            'orders_this_month' => [
                'count' => $ordersCount,
                'average_days_between_orders' => $averageDaysBetweenOrders,
            ],
            'total_savings' => $totalSavings + $mealSavings,
            'average_order_value' => $ordersCount > 0 ? round($monthlySpend / $ordersCount, 2) : 0,
        ];
    }

    private function getCategoryDistribution(User $user): array
    {
        $orderItems = OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id)->where('status', '!=', 'cancelled'))
            ->with('meal.category')
            ->get();

        $categoryTotals = [];
        $totalItems = 0;

        foreach ($orderItems as $item) {
            if ($item->meal?->category) {
                $catId = $item->meal->category->id;
                $categoryTotals[$catId] = $categoryTotals[$catId] ?? [
                    'category_id' => $catId,
                    'category_name' => $item->meal->category->name,
                    'total_quantity' => 0,
                ];
                $categoryTotals[$catId]['total_quantity'] += $item->quantity;
                $totalItems += $item->quantity;
            }
        }

        return collect($categoryTotals)->map(function ($data) use ($totalItems) {
            $data['percentage'] = $totalItems > 0 ? round(($data['total_quantity'] / $totalItems) * 100, 1) : 0;
            return $data;
        })->sortByDesc('percentage')->values()->toArray();
    }

    private function getRecentOrders(User $user, int $limit): array
    {
        return Order::where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_description' => $order->status_description,
                'total' => (float) $order->total,
                'created_at' => $order->created_at,
                'items_count' => $order->items->sum('quantity'),
            ])->toArray();
    }

    private function getTopPurchases(User $user, int $limit): array
    {
        return OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id)->where('status', '!=', 'cancelled'))
            ->with('meal.category', 'meal.subcategory')
            ->select('meal_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_spent'))
            ->groupBy('meal_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'meal_id' => $item->meal?->id,
                'title' => $item->meal?->title,
                'image_url' => $item->meal?->image_url,
                'category' => $item->meal?->category ? [
                    'id' => $item->meal->category->id,
                    'name' => $item->meal->category->name,
                ] : null,
                'total_quantity_purchased' => (int) $item->total_quantity,
                'total_spent' => (float) $item->total_spent,
            ])->toArray();
    }
}