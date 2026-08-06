<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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
            ...($this->getApiPriceAttributes() ?? []),
            'has_offer' => $this->hasOffer(),

            // Rating & Details
            'rating' => isset($this->rating) ? (float) $this->rating : null,
            'rating_count' => isset($this->rating_count) ? (int) $this->rating_count : null,

            // Product details
            'size' => $this->size,
            'brand' => $this->brand,
            'includes' => $this->includes,
            'how_to_use' => $this->how_to_use,
            'features' => $this->features,

            // Expiry and availability
            'expiry_date' => $this->expiry_date,
            'days_until_expiry' => method_exists($this, 'daysUntilExpiry') ? $this->daysUntilExpiry() : null,
            'is_expired' => method_exists($this, 'isExpired') ? $this->isExpired() : null,

            // Stock
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => method_exists($this, 'isInStock') ? $this->isInStock() : null,
            'sold_count' => $this->sold_count,

            // Status
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available ?? null,
            'available_date' => $this->available_date,

            // Relationships
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),

            'subcategory' => $this->whenLoaded('subcategory', function () {
                return $this->subcategory ? [
                    'id' => $this->subcategory->id,
                    'name' => $this->subcategory->name,
                    'slug' => $this->subcategory->slug,
                ] : null;
            }),

            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user' => $review->relationLoaded('user') && $review->user ? [
                            'id' => $review->user->id,
                            'name' => $review->user->full_name ?? $review->user->username ?? 'User',
                        ] : null,
                        'rating' => (int) $review->rating,
                        'comment' => $review->comment,
                        'images' => $review->images ?? [],
                        'created_at' => $review->created_at?->toIso8601String(),
                    ];
                })->values();
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
