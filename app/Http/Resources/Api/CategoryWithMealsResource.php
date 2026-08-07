<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWithMealsResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'sort_order' => $this->sort_order,
            'meals' => $this->meals->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,
                    'has_offer' => $meal->hasOffer(),
                    'is_featured' => $meal->is_featured,
                    'features' => $meal->features,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
