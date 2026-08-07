<?php

namespace App\Action\Api;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class GetDashboardTopPurchasesAction
{
    public function execute($user, int $limit = 10): array
    {
        $topMeals = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled');
        })
            ->with('meal.category', 'meal.subcategory')
            ->select('meal_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_spent'))
            ->groupBy('meal_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        return $topMeals->map(function ($item) {
            $meal = $item->meal;
            return [
                'meal_id' => $meal?->id,
                'title' => $meal?->title,
                'image_url' => $meal?->image_url ?? null,
                'category' => $meal?->category ? [
                    'id' => $meal->category->id,
                    'name' => $meal->category->name,
                ] : null,
                'total_quantity_purchased' => (int) $item->total_quantity,
                'total_spent' => (float) $item->total_spent,
            ];
        })->toArray();
    }
}