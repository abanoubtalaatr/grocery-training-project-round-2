<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderMealResource extends JsonResource
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
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'features' => $this->features,
            'available_date' => $this->available_date,
            'created_at' => $this->created_at,
        ];
    }
}