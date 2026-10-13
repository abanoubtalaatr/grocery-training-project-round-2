<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
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
            'title' => $this->localized_title,
            'title_ar' => $this->title_ar,
            'slug' => $this->slug,
            'description' => $this->localized_description,
            'description_ar' => $this->description_ar,
            'image_url' => $this->image_url,
            'offer_title' => $this->offer_title,
        ];
    }
}
