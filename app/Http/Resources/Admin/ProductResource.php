<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'images' => $this->images ?? [],
            'offer_title' => $this->offer_title,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price ? (float) $this->discount_price : null,
            'final_price' => (float) $this->final_price,
            'has_offer' => $this->hasOffer(),
            'category' => $this->when($this->relationLoaded('category') && $this->category, [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'subcategory' => $this->when($this->relationLoaded('subcategory') && $this->subcategory, [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug,
            ]),
            'size' => $this->size,
            'brand' => $this->brand,
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => $this->isInStock(),
            'sold_count' => $this->sold_count,
            'is_available' => $this->is_available,
            'is_featured' => $this->is_featured,
            'rating' => (float) $this->rating,
            'rating_count' => (int) $this->rating_count,
            'features' => $this->features,
            'expiry_date' => $this->expiry_date,
            'available_date' => $this->available_date,
            'reviews' => $this->when($this->relationLoaded('reviews'), 
                $this->reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'user' => $review->user ? $review->user->full_name : null,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                ])
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
