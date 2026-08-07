<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryShowResource extends JsonResource
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
            'order' => $this->order,
            'is_active' => $this->is_active,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'meals' => $this->meals->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'image_url' => $meal->image_url,
                    ...$meal->getApiPriceAttributes(),
                    'rating' => (float) $meal->rating,
                    'is_featured' => $meal->is_featured,
                    'features' => $meal->features,
                ];
            }),
            'meals_count' => $this->meals()->available()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
