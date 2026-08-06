<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealRecommendationResource extends JsonResource
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
            'is_featured' => $this->is_featured,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'features' => $this->features,
            'recommendation_reason' => $this->recommendation_reason ?? '',
        ];
    }
}
