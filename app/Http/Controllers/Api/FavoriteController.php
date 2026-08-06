<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Action\Api\ListFavoritesAction;
use App\Action\Api\ToggleFavoriteAction;
use App\Action\Api\CheckFavoriteAction;
use App\Action\Api\RemoveFavoriteAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FavoriteController extends Controller
{
    /**
     * Get all user's favorite meals
     */
    public function index(Request $request, ListFavoritesAction $action): JsonResponse
    {
        try {
            $user = $request->user();

            $favorites = $action->execute($user);

            return response()->json([
                'success' => true,
                'message' => 'Favorites retrieved successfully',
                'data' => $favorites,
                'total_count' => $favorites->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve favorites',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle favorite status for a meal
     */
    public function toggle(Request $request, string $mealId, ToggleFavoriteAction $action): JsonResponse
    {
        try {
            $user = $request->user();

            $result = $action->execute($user, $mealId);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Toggled favorite',
                'data' => [
                    'meal_id' => $result['meal_id'] ?? $mealId,
                    'is_favorited' => $result['is_favorited'] ?? false,
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle favorite',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a meal is favorited
     */
    public function check(Request $request, string $mealId, CheckFavoriteAction $action): JsonResponse
    {
        try {
            $user = $request->user();

            $result = $action->execute($user, $mealId);

            return response()->json([
                'success' => true,
                'data' => [
                    'meal_id' => $result['meal_id'],
                    'is_favorited' => $result['is_favorited'],
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check favorite status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove meal from favorites
     */
    public function remove(Request $request, string $mealId, RemoveFavoriteAction $action): JsonResponse
    {
        try {
            $user = $request->user();

            $deleted = $action->execute($user, $mealId);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Removed from favorites',
                    'data' => [
                        'meal_id' => $mealId,
                        'is_favorited' => false,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Meal was not in favorites',
            ], 404);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove from favorites',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
