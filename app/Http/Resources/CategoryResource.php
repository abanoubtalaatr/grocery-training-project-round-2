<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'slug' => $this->slug,

            'description' => $this->description,

            'image' => $this->image,

            'is_active' => $this->is_active,

            'sort_order' => $this->sort_order,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}