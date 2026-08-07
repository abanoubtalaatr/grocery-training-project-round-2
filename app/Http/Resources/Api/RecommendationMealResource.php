<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationMealResource extends JsonResource
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
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'features' => $this->features,
            'recommendation_reason' => $this->getRecommendationReason(),
        ];
    }

    private function getRecommendationReason(): string
    {
        if ($this->is_featured && $this->discount_price) {
            return 'Featured with special offer';
        }

        if ($this->is_featured) {
            return 'Featured meal';
        }

        if ($this->discount_price) {
            return 'Special offer';
        }

        return 'Popular choice';
    }
}