<?php

namespace App\Actions\Api\Favorite;

use App\Models\Meal;
use App\Models\User;

class CheckFavoriteAction
{
    public function run(User $user, Meal $meal): array
    {
        return [
            'meal_id' => $meal->id,
            'is_favorited' => $user->favorites()
                ->where('meal_id', $meal->id)
                ->exists(),
        ];
    }
}