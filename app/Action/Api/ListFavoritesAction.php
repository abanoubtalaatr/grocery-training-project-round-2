<?php

namespace App\Action\Api;

class ListFavoritesAction
{
    public function execute($user)
    {
        $favorites = $user->favorites()
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
                    ...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,
                    'size' => $meal->size,
                    'brand' => $meal->brand,
                    'stock_quantity' => $meal->stock_quantity,
                    'in_stock' => $meal->isInStock(),
                    'is_available' => $meal->is_available,
                    'is_featured' => $meal->is_featured,
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

        return $favorites;
    }
}
