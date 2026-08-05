<?php

namespace App\Action\Favorite;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListFavoritesAction
{
    public function handle($user, int $perPage = 15)
    {
        if (method_exists($user, 'favorites')) {
            return $user->favorites()->with('category', 'subcategory')->paginate(min(max($perPage,1),50));
        }

        // Fallback: query meals via favorites table
        $query = \App\Models\Meal::query()
            ->join('favorites', 'meals.id', '=', 'favorites.meal_id')
            ->where('favorites.user_id', $user->id)
            ->select('meals.*');

        return $query->paginate(min(max($perPage,1),50));
    }
}
