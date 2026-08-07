<?php

namespace App\Http\Controllers\Api;

use App\Actions\Favorite\ToggleFavoriteAction;
use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponse;

    /**
     * Get all user's favorite meals
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()->favorites()
            ->with(['meal.category', 'meal.subcategory'])
            ->latest()
            ->get()
            ->map(function ($favorite) {
                $meal = $favorite->meal;

                return [
                    'id'             => $meal->id,
                    'title'          => $meal->title,
                    'slug'           => $meal->slug,
                    'description'    => $meal->description,
                    'image_url'      => $meal->image_url,
                    'offer_title'    => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'has_offer'      => $meal->hasOffer(),
                    'rating'         => (float) $meal->rating,
                    'rating_count'   => (int) $meal->rating_count,
                    'size'           => $meal->size,
                    'brand'          => $meal->brand,
                    'stock_quantity' => $meal->stock_quantity,
                    'in_stock'       => $meal->isInStock(),
                    'is_available'   => $meal->is_available,
                    'is_featured'    => $meal->is_featured,
                    'category'       => [
                        'id'   => $meal->category->id,
                        'name' => $meal->category->name,
                        'slug' => $meal->category->slug,
                    ],
                    'subcategory'    => $meal->subcategory ? [
                        'id'   => $meal->subcategory->id,
                        'name' => $meal->subcategory->name,
                        'slug' => $meal->subcategory->slug,
                    ] : null,
                    'is_favorited'   => true,
                    'favorited_at'   => $favorite->created_at,
                ];
            });

        return response()->json([
            'success'     => true,
            'message'     => 'Favorites retrieved successfully',
            'data'        => $favorites,
            'total_count' => $favorites->count(),
        ]);
    }

    /**
     * Toggle favorite status for a meal
     */
    public function toggle(Request $request, Meal $meal, ToggleFavoriteAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $meal);

        return $this->success([
            'meal_id'      => $meal->id,
            'is_favorited' => $result['is_favorited'],
        ], $result['message']);
    }

    /**
     * Check if a meal is favorited
     */
    public function check(Request $request, Meal $meal): JsonResponse
    {
        $isFavorited = $request->user()->favorites()->where('meal_id', $meal->id)->exists();

        return $this->success([
            'meal_id'      => $meal->id,
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * Remove meal from favorites
     */
    public function remove(Request $request, Meal $meal): JsonResponse
    {
        $deleted = $request->user()->favorites()->where('meal_id', $meal->id)->delete();

        if (! $deleted) {
            return $this->notFound('Meal was not in favorites');
        }

        return $this->success([
            'meal_id'      => $meal->id,
            'is_favorited' => false,
        ], 'Removed from favorites');
    }
}
