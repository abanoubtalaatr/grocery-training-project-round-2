<?php

namespace App\Actions\Admin\Monitoring\GetSystemRisksAction;

namespace App\Actions\Admin\Monitoring;

use App\Models\Meal;
use App\Models\Order;
use App\Models\User;

class GetSystemRisksAction
{
    public function run(): array
    {
        // Stock Risks
        $outOfStock  = Meal::whereNull('deleted_at')->where('stock_quantity', '<=', 0)->with('category')->get();
        $lowStock    = Meal::whereNull('deleted_at')->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->with('category')->get();
        $expiringSoon = Meal::whereNull('deleted_at')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->with('category')
            ->orderBy('expiry_date')
            ->get();
        $expiredMeals = Meal::whereNull('deleted_at')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with('category')
            ->orderBy('expiry_date')
            ->get();

        // Stalled Orders (stuck in placed for > 24h)
        $stalledOrders = Order::where('status', 'placed')
            ->where('placed_at', '<', now()->subHours(24))
            ->orWhere(function ($q) {
                $q->where('status', 'placed')
                  ->whereNull('placed_at')
                  ->where('created_at', '<', now()->subHours(24));
            })
            ->with('user')
            ->orderBy('created_at')
            ->take(20)
            ->get();

        // Inactive users (no orders in 90 days)
        $inactiveUserCount = User::where('is_admin', false)
            ->where(function ($q) {
                $q->doesntHave('orders')
                  ->orWhereHas('orders', function ($o) {
                      $o->where('created_at', '<', now()->subDays(90));
                  }, '<', 1);
            })
            ->count();

        // System summary
        $summaryStats = [
            'out_of_stock'     => $outOfStock->count(),
            'low_stock'        => $lowStock->count(),
            'expiring_soon'    => $expiringSoon->count(),
            'expired'          => $expiredMeals->count(),
            'stalled_orders'   => $stalledOrders->count(),
            'inactive_users'   => $inactiveUserCount,
        ];

        return compact('outOfStock', 'lowStock', 'expiringSoon', 'expiredMeals', 'stalledOrders', 'summaryStats');
    }
}
