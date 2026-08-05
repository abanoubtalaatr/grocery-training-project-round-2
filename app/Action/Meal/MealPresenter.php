<?php

namespace App\Action\Meal;

class MealPresenter
{
    public function presentSmall($meal): array
    {
        return [
            'id' => $meal->id,
            'title' => $meal->title,
            'slug' => $meal->slug,
            'description' => $meal->description,
            'image_url' => $meal->image_url,
            'offer_title' => $meal->offer_title,
            ...$meal->getApiPriceAttributes(),
            'has_offer' => $meal->hasOffer(),
            'is_featured' => $meal->is_featured,
            'features' => $meal->features,
            'available_date' => $meal->available_date ?? null,
            'created_at' => $meal->created_at,
        ];
    }

    public function presentListItem($meal, array $extra = []): array
    {
        $base = [
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
            'is_featured' => $meal->is_featured,
            'sold_count' => $meal->sold_count,
            'category' => $meal->category ? ['id' => $meal->category->id, 'name' => $meal->category->name] : null,
            'subcategory' => $meal->subcategory ? ['id' => $meal->subcategory->id, 'name' => $meal->subcategory->name] : null,
            'features' => $meal->features,
            'created_at' => $meal->created_at,
        ];

        return array_merge($base, $extra);
    }

    public function presentDetail($meal): array
    {
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
            'includes' => $meal->includes,
            'how_to_use' => $meal->how_to_use,
            'features' => $meal->features,
            'expiry_date' => $meal->expiry_date,
            'days_until_expiry' => $meal->daysUntilExpiry(),
            'is_expired' => $meal->isExpired(),
            'stock_quantity' => $meal->stock_quantity,
            'in_stock' => $meal->isInStock(),
            'sold_count' => $meal->sold_count,
            'is_featured' => $meal->is_featured,
            'is_available' => $meal->is_available,
            'available_date' => $meal->available_date,
            'category' => $meal->category ? ['id' => $meal->category->id, 'name' => $meal->category->name, 'slug' => $meal->category->slug] : null,
            'subcategory' => $meal->subcategory ? ['id' => $meal->subcategory->id, 'name' => $meal->subcategory->name, 'slug' => $meal->subcategory->slug] : null,
            'reviews' => $meal->relationLoaded('reviews') ? $meal->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'user' => $review->relationLoaded('user') && $review->user ? ['id' => $review->user->id, 'name' => $review->user->full_name ?? $review->user->username ?? 'User'] : null,
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                    'images' => $review->images ?? [],
                    'created_at' => $review->created_at?->toIso8601String(),
                ];
            })->values() : [],
            'created_at' => $meal->created_at,
            'updated_at' => $meal->updated_at,
        ];
    }
}
