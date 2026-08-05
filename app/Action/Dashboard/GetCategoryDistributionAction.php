<?php

namespace App\Action\Dashboard;

use Illuminate\Support\Facades\DB;

class GetCategoryDistributionAction
{
    public function handle($user): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('meals', 'order_items.meal_id', '=', 'meals.id')
            ->join('categories', 'meals.category_id', '=', 'categories.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', '!=', 'cancelled')
            ->select('meals.category_id', 'categories.name as category_name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->groupBy('meals.category_id', 'categories.name')
            ->get();

        $totalItems = $rows->sum('total_quantity');

        $distribution = $rows->map(function ($r) use ($totalItems) {
            $percentage = $totalItems > 0 ? round(($r->total_quantity / $totalItems) * 100, 1) : 0;
            return [
                'category_id' => $r->category_id,
                'category_name' => $r->category_name,
                'total_quantity' => (int) $r->total_quantity,
                'percentage' => $percentage,
            ];
        })->sortByDesc('percentage')->values()->all();

        return $distribution;
    }
}
