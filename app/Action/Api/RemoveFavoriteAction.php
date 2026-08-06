<?php

namespace App\Action\Api;

class RemoveFavoriteAction
{
    public function execute($user, $mealId): bool
    {
        $favorite = $user->favorites()->where('meal_id', $mealId)->first();

        if (! $favorite) {
            return false;
        }

        return (bool) $favorite->delete();
    }
}
