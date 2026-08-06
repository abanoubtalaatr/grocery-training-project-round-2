<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryDetailsResource extends JsonResource
{
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

            'meals' => MealResource::collection($this->meals),

            'meals_count' => $this->meals_count,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}