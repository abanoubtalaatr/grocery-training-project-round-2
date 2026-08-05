<?php

namespace App\Action\Dashboard;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class GetTopPurchasesAction
{
    public function handle($user, int $limit = 10): array
    {
        $topMeals = OrderItem::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', '!=', 'cancelled');
            })
            ->select('meal_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_spent'))
            ->groupBy('meal_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        $mealIds = $topMeals->pluck('meal_id')->filter()->values()->all();
        $meals = \App\Models\Meal::whereIn('id', $mealIds)->with(['category:id,name', 'subcategory:id,name'])->get()->keyBy('id');

        return $topMeals->map(function ($item) use ($meals) {
            $meal = $meals->get($item->meal_id);
            return [
                'meal_id' => $meal?->id,
                'title' => $meal?->title,
                'image_url' => $meal?->image_url ?? null,
                'category' => $meal?->category ? ['id' => $meal->category->id, 'name' => $meal->category->name] : null,
                'total_quantity_purchased' => (int) $item->total_quantity,
                'total_spent' => (float) $item->total_spent,
            ];
        })->toArray();
    }
}
