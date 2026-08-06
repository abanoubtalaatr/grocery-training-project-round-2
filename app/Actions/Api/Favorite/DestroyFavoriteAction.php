<?php

namespace App\Actions\Api\Favorite;

use App\Models\User;

class DestroyFavoriteAction
{
    public function run(User $user, int $mealId): void
    {
        $user->favorites()
            ->where('meal_id', $mealId)
            ->delete();
    }
}