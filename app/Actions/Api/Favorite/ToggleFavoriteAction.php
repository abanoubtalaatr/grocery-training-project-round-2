<?php

namespace App\Actions\Api\Favorite;

use App\Models\Meal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ToggleFavoriteAction
{
    public function execute(Meal $meal): array
    {
        $user = Auth::user();
        $isFavorited = false;
        $message = '';

        DB::transaction(function () use ($meal, $user, &$isFavorited, &$message) {
            $favorite = $user->favorites()->where('meal_id', $meal->id)->first();

            if ($favorite) {
                $favorite->delete();
                $isFavorited = false;
                $message = 'Removed from favorites';
            } else {
                $user->favorites()->create(['meal_id' => $meal->id]);
                $isFavorited = true;
                $message = 'Added to favorites';
            }
        });

        return [
            'meal_id' => $meal->id,
            'is_favorited' => $isFavorited,
            'message' => $message,
        ];
    }
}
