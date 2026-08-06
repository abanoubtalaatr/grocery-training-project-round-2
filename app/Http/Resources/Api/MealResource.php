<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image_url' => $this->image_url,

            ...$this->getApiPriceAttributes(),

            'rating' => (float) $this->rating,
            'size' => $this->size,
            'brand' => $this->brand,
            'stock_quantity' => $this->stock_quantity,
            'is_available' => $this->is_available,
            'in_stock' => $this->isInStock(),

            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,

            'subcategory' => $this->subcategory ? [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
            ] : null,
        ];
    }
}