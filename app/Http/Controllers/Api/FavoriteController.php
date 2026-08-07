<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        protected FavoriteService $favoriteService
    ) {}

    /**
     * Get all user's favorite meals.
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $this->favoriteService->getUserFavorites($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Favorites retrieved successfully',
            'data' => $favorites,
            'total_count' => $favorites->count(),
        ]);
    }

    /**
     * Toggle favorite status for a meal.
     */
    public function toggle(Request $request, Meal $meal): JsonResponse
    {
        $result = $this->favoriteService->toggleFavorite($request->user(), $meal);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'meal_id' => $meal->id,
                'is_favorited' => $result['is_favorited'],
            ],
        ]);
    }

    /**
     * Check if a meal is favorited.
     */
    public function check(Request $request, Meal $meal): JsonResponse
    {
        $isFavorited = $this->favoriteService->isFavorited($request->user(), $meal);

        return response()->json([
            'success' => true,
            'data' => [
                'meal_id' => $meal->id,
                'is_favorited' => $isFavorited,
            ],
        ]);
    }

    /**
     * Remove meal from favorites.
     */
    public function remove(Request $request, Meal $meal): JsonResponse
    {
        $removed = $this->favoriteService->removeFavorite($request->user(), $meal);

        if (!$removed) {
            return response()->json([
                'success' => false,
                'message' => 'Meal was not in favorites',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Removed from favorites',
            'data' => [
                'meal_id' => $meal->id,
                'is_favorited' => false,
            ],
        ]);
    }
}