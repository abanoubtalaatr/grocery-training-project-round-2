<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Favorite\ToggleFavoriteAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FavoriteResource;
use App\Models\Favorite;
use App\Models\Meal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    use ApiResponse;

    /**
     * Get all user's favorite meals
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = Auth::user()->favorites()
            ->with(['meal.category', 'meal.subcategory'])
            ->latest()
            ->get();

        return $this->success(
            FavoriteResource::collection($favorites),
            'Favorites retrieved successfully'
        );
    }

    /**
     * Get specific favorite (check if meal is favorited)
     */
    public function show(Request $request, string $mealId): JsonResponse
    {
        $meal = Meal::findOrFail($mealId);
        $isFavorited = Auth::user()->favorites()
            ->where('meal_id', $meal->id)
            ->exists();

        return $this->success([
            'meal_id' => $meal->id,
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * Toggle favorite status for a meal
     */
    public function store(Request $request, Meal $meal, ToggleFavoriteAction $action): JsonResponse
    {
        $this->authorize('create', Favorite::class);

        $result = $action->execute($meal);

        $statusCode = $result['is_favorited'] ? 201 : 200;

        return $this->success(
            [
                'meal_id' => $result['meal_id'],
                'is_favorited' => $result['is_favorited'],
            ],
            $result['message'],
            $statusCode
        );
    }

    /**
     * Remove meal from favorites
     */
    public function destroy(Request $request, Meal $meal): JsonResponse
    {
        $favorite = Auth::user()->favorites()
            ->where('meal_id', $meal->id)
            ->first();

        if (!$favorite) {
            return $this->error('Meal was not in favorites', 404);
        }

        $this->authorize('delete', $favorite);

        $favorite->delete();

        return $this->success(null, 'Removed from favorites');
    }
}
