<?php

namespace App\Action\Api;

class ToggleFavoriteAction
{
    public function execute($user, $mealId): array
    {
        $meal = \App\Models\Meal::findOrFail($mealId);

        return \DB::transaction(function () use ($user, $meal) {
            $favorite = $user->favorites()->where('meal_id', $meal->id)->first();

            if ($favorite) {
                $favorite->delete();
                return ['meal_id' => $meal->id, 'is_favorited' => false, 'message' => 'Removed from favorites'];
            }

            $user->favorites()->create(['meal_id' => $meal->id]);
            return ['meal_id' => $meal->id, 'is_favorited' => true, 'message' => 'Added to favorites'];
        });
    }
}
