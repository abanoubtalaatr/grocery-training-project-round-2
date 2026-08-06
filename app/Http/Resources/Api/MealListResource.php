<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealListResource extends JsonResource
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
            ...$this->getApiPriceAttributes(),
            'has_offer' => $this->hasOffer(),
            'rating' => (float) $this->rating,
            'rating_count' => (int) $this->rating_count,
            'size' => $this->size,
            'brand' => $this->brand,
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => $this->isInStock(),
            'is_featured' => $this->is_featured,
            'sold_count' => $this->sold_count,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'subcategory' => $this->subcategory ? [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
            ] : null,
            'features' => $this->features,
            'is_favorited' => $this->is_favorited ?? false,
            'created_at' => $this->created_at,
        ];
    }
}
