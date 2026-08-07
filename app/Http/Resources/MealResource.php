<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $favoriteMealIds = $request->attributes->get('favorite_meal_ids', []);

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
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug ?? null,
            ]),
            'subcategory' => $this->whenLoaded('subcategory', fn () => $this->subcategory ? [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug ?? null,
            ] : null),
            'features' => $this->features,
            'available_date' => $this->available_date,
            'is_favorited' => in_array($this->id, $favoriteMealIds, true),
            'order_count' => $this->when(isset($this->order_count), (int) $this->order_count),
            'created_at' => $this->created_at,
        ];
    }
}