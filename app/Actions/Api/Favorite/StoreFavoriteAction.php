<?php

namespace App\Actions\Api\Favorite;

use App\Models\User;

class StoreFavoriteAction
{
    public function run(User $user, int $mealId)
    {
        return $user->favorites()->firstOrCreate([
            'meal_id' => $mealId,
        ])->load([
            'meal.category',
            'meal.subcategory',
        ]);
    }
}