<?php

namespace App\Action\Api;

class CheckFavoriteAction
{
    public function execute($user, $mealId): array
    {
        $meal = \App\Models\Meal::findOrFail($mealId);

        $isFavorited = $user->favorites()->where('meal_id', $meal->id)->exists();

        return ['meal_id' => $meal->id, 'is_favorited' => $isFavorited];
    }
}
