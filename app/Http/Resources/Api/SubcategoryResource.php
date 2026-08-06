<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource
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
            'category' => $this->when($this->relationLoaded('category') && $this->category, [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'meals_count' => $this->when($this->relationLoaded('meals'), $this->meals->count()),
            'created_at' => $this->created_at,
        ];
    }
}