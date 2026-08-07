<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'description'  => $this->description,
            'image_url'    => $this->image_url,
            'offer_title'  => $this->offer_title,

            // Pricing
            ...$this->getApiPriceAttributes(),
            'has_offer'    => $this->hasOffer(),

            // Rating
            'rating'       => (float) $this->rating,
            'rating_count' => (int) $this->rating_count,

            // Product details
            'size'         => $this->size,
            'brand'        => $this->brand,
            'includes'     => $this->when(isset($this->includes), $this->includes),
            'how_to_use'   => $this->when(isset($this->how_to_use), $this->how_to_use),
            'features'     => $this->features,

            // Stock
            'stock_quantity' => $this->stock_quantity,
            'in_stock'       => $this->isInStock(),
            'is_available'   => $this->is_available,
            'is_featured'    => $this->is_featured,
            'sold_count'     => $this->sold_count,

            // Expiry
            'expiry_date'     => $this->when(isset($this->expiry_date), $this->expiry_date),
            'days_until_expiry' => $this->when(isset($this->expiry_date), fn () => $this->daysUntilExpiry()),
            'is_expired'      => $this->when(isset($this->expiry_date), fn () => $this->isExpired()),
            'available_date'  => $this->available_date,

            // Relationships
            'category' => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'subcategory' => $this->whenLoaded('subcategory', fn () => $this->subcategory ? [
                'id'   => $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug,
            ] : null),

            // Timestamps
            'created_at'  => $this->created_at,
            'updated_at'  => $this->when(isset($this->updated_at), $this->updated_at),
        ];
    }
}
