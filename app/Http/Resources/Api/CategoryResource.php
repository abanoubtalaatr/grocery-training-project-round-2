<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized_name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'description' => $this->localized_description,
            'description_ar' => $this->description_ar,
            'image_url' => $this->image_url,
            'meals_count' => $this->whenCounted('meals'),
            'sort_order' => $this->sort_order,
            'meals' => CategoryMealResource::collection($this->whenLoaded('meals')),
            'created_at' => $this->created_at,
        ];
    }
}
