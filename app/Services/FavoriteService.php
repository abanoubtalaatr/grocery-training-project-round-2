<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FavoriteService
{
    /**
     * Get all favorited meals with transformed payload.
     */
    public function getUserFavorites(User $user): Collection
    {
        return $user->favorites()
            ->with(['meal.category', 'meal.subcategory'])
            ->latest()
            ->get()
            ->map(function ($favorite) {
                $meal = $favorite->meal;
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,

                    // Pricing
                    ...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),

                    // Rating & Details
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,
                    'size' => $meal->size,
                    'brand' => $meal->brand,

                    // Stock & Availability
                    'stock_quantity' => $meal->stock_quantity,
                    'in_stock' => $meal->isInStock(),
                    'is_available' => $meal->is_available,
                    'is_featured' => $meal->is_featured,

                    // Category & Subcategory
                    'category' => $meal->category ? [
                        'id' => $meal->category->id,
                        'name' => $meal->category->name,
                        'slug' => $meal->category->slug,
                    ] : null,
                    'subcategory' => $meal->subcategory ? [
                        'id' => $meal->subcategory->id,
                        'name' => $meal->subcategory->name,
                        'slug' => $meal->subcategory->slug,
                    ] : null,

                    'is_favorited' => true,
                    'favorited_at' => $favorite->created_at,
                ];
            });
    }

    /**
     * Toggle favorite status for a meal.
     */
    public function toggleFavorite(User $user, Meal $meal): array
    {
        return DB::transaction(function () use ($user, $meal) {
            $favorite = $user->favorites()->where('meal_id', $meal->id)->first();

            if ($favorite) {
                $favorite->delete();
                return [
                    'is_favorited' => false,
                    'message' => 'Removed from favorites',
                ];
            }

            $user->favorites()->create([
                'meal_id' => $meal->id,
            ]);

            return [
                'is_favorited' => true,
                'message' => 'Added to favorites',
            ];
        });
    }

    /**
     * Check if a meal is favorited by the user.
     */
    public function isFavorited(User $user, Meal $meal): bool
    {
        return $user->favorites()->where('meal_id', $meal->id)->exists();
    }

    /**
     * Remove meal from favorites.
     */
    public function removeFavorite(User $user, Meal $meal): bool
    {
        return (bool) $user->favorites()->where('meal_id', $meal->id)->delete();
    }
}