<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'offer_title' => $this->offer_title,

            // Pricing
            ...$this->getApiPriceAttributes(),
            'has_offer' => $this->hasOffer(),

            // Rating
            'rating' => (float) $this->rating,
            'rating_count' => (int) $this->rating_count,

            // Product details
            'size' => $this->size,
            'brand' => $this->brand,
            'includes' => $this->includes,
            'how_to_use' => $this->how_to_use,
            'features' => $this->features,

            // Expiry and availability
            'expiry_date' => $this->expiry_date,
            'days_until_expiry' => $this->daysUntilExpiry(),
            'is_expired' => $this->isExpired(),

            // Stock
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => $this->isInStock(),
            'sold_count' => $this->sold_count,

            // Status
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available,
            'available_date' => $this->available_date,

            // Relationships
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'subcategory' => $this->whenLoaded('subcategory', fn () => $this->subcategory ? [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug,
            ] : null),
            'reviews' => $this->whenLoaded('reviews', fn () => $this->reviews->map(fn ($review) => [
                'id' => $review->id,
                'user' => $review->relationLoaded('user') && $review->user ? [
                    'id' => $review->user->id,
                    'name' => $review->user->full_name ?? $review->user->username ?? 'User',
                ] : null,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'images' => $review->images ?? [],
                'created_at' => $review->created_at?->toIso8601String(),
            ])),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
