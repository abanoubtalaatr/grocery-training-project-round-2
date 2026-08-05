<?php

namespace App\Action\Favorite;

use App\Models\Meal;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddFavoriteAction
{
    public function handle($user, int $mealId): array
    {
        $meal = Meal::find($mealId);
        if (! $meal) throw new ModelNotFoundException('Meal not found');

        // Use favorites relation if it exists
        if (method_exists($user, 'favorites')) {
            $user->favorites()->syncWithoutDetaching([$mealId]);
        } else {
            // Fallback: attach to pivot table favorites
            \DB::table('favorites')->insertOrIgnore(['user_id' => $user->id, 'meal_id' => $mealId]);
        }

        return ['success' => true, 'message' => 'Added to favorites', 'meal_id' => $mealId];
    }
}
