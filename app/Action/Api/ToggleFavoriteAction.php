<?php

namespace App\Action\Api;

use App\Models\Meal;
use Illuminate\Support\Facades\DB;

class ToggleFavoriteAction
{
    public function execute($user, Meal $meal): array
    {
        DB::beginTransaction();

        try {
            $favorite = $user->favorites()->where('meal_id', $meal->id)->first();

            if ($favorite) {
                $favorite->delete();
                $isFavorited = false;
            } else {
                $user->favorites()->create([
                    'meal_id' => $meal->id,
                ]);
                $isFavorited = true;
            }

            DB::commit();

            return [
                'meal_id' => $meal->id,
                'is_favorited' => $isFavorited,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}