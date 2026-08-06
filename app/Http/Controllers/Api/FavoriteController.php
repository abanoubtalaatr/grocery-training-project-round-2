<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\ToggleFavoriteAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FavoriteMealResource;
use App\Models\Meal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = $user->favorites()
            ->with(['meal.category', 'meal.subcategory'])
            ->latest()
            ->get();

        return $this->success(FavoriteMealResource::collection($favorites),'Favorites retrieved successfully');
    }

    public function toggle(Request $request, string $mealId, ToggleFavoriteAction $action): JsonResponse
    {
        $meal = Meal::findOrFail($mealId);
        $result = $action->execute($request->user(), $meal);

        return $this->success($result,$result['is_favorited'] ? 'Added to favorites' : 'Removed from favorites');
    }

    public function check(Request $request, string $mealId): JsonResponse
    {
        $user = $request->user();
        $meal = Meal::findOrFail($mealId);

        $isFavorited = $user->favorites()->where('meal_id', $meal->id)->exists();

        return $this->success(
            [
                'meal_id' => $meal->id,
                'is_favorited' => $isFavorited,
            ],
            'Favorite status checked successfully'
        );
    }

    public function remove(Request $request, string $mealId): JsonResponse
    {
        $user = $request->user();
        $meal = Meal::findOrFail($mealId);

        $deleted = $user->favorites()->where('meal_id', $meal->id)->delete();

        if (!$deleted) {
            return $this->success(
                [
                    'meal_id' => $meal->id,
                    'is_favorited' => false,
                ],
                'Meal was not in favorites'
            );
        }

        return $this->success(
            [
                'meal_id' => $meal->id,
                'is_favorited' => false,
            ],
            'Removed from favorites'
        );
    }
}
