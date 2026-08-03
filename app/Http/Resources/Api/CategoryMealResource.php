<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryMealResource extends JsonResource
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
            'rating' => (float) $this->rating,
            'rating_count' => (int) $this->rating_count,
            'has_offer' => $this->hasOffer(),
            'is_featured' => (bool) $this->is_featured,
            'features' => $this->features,
        ];
    }
}
