<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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
            ...$this->getApiPriceAttributes(),
            'has_offer' => $this->hasOffer(),

            // Rating
            'rating' => (float) $this->rating,
            'rating_count' => (int) $this->rating_count,

            // Details
            'size' => $this->size,
            'brand' => $this->brand,

            // Stock
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => $this->isInStock(),
            'is_featured' => $this->is_featured,

            // Expiry
            'expiry_date' => $this->expiry_date,
            'days_until_expiry' => $this->daysUntilExpiry(),
            'is_expired' => $this->isExpired(),

            // Features
            'features' => $this->features,

            // Relation
            'subcategory' => $this->whenLoaded('subcategory', function () {
                return [
                    'id' => $this->subcategory->id,
                    'name' => $this->subcategory->name,
                    'slug' => $this->subcategory->slug,
                ];
            }),
        ];
    }
}