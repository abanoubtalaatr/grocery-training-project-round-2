<?php

namespace App\Action\Favorite;

class RemoveFavoriteAction
{
    public function handle($user, int $mealId): array
    {
        if (method_exists($user, 'favorites')) {
            $user->favorites()->detach($mealId);
        } else {
            \DB::table('favorites')->where(['user_id' => $user->id, 'meal_id' => $mealId])->delete();
        }

        return ['success' => true, 'message' => 'Removed from favorites', 'meal_id' => $mealId];
    }
}
