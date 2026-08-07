<?php

namespace App\Action\Admin\Order;

use App\Models\Order;
use Carbon\Carbon;

class GetOrderStatsAction
{
    public function execute(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $statuses = ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled'];
        $statsByStatus = [];

        foreach ($statuses as $status) {
            $statsByStatus[$status] = Order::where('status', $status)->count();
        }

        return [
            'total' => Order::count(),
            'today' => Order::whereDate('created_at', $today)->count(),
            'this_month' => Order::where('created_at', '>=', $thisMonth)->count(),
            'total_revenue' => round(Order::where('status', '!=', 'cancelled')->sum('total'), 2),
            'revenue_this_month' => round(Order::where('status', '!=', 'cancelled')
                ->where('created_at', '>=', $thisMonth)
                ->sum('total'), 2),
            'average_order_value' => round(Order::where('status', '!=', 'cancelled')->avg('total') ?? 0, 2),
            'by_status' => $statsByStatus,
        ];
    }
}
