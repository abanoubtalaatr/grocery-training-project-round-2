<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'meal'            => [
                'id'          => $this->meal->id,
                'title'       => $this->meal->title,
                'slug'        => $this->meal->slug,
                'image_url'   => $this->meal->image_url,
                ...$this->meal->getApiPriceAttributes(),
                'category'    => $this->meal->relationLoaded('category') && $this->meal->category ? [
                    'id'   => $this->meal->category->id,
                    'name' => $this->meal->category->name,
                ] : null,
                'subcategory' => $this->meal->relationLoaded('subcategory') && $this->meal->subcategory ? [
                    'id'   => $this->meal->subcategory->id,
                    'name' => $this->meal->subcategory->name,
                ] : null,
            ],
            'quantity'        => $this->quantity,
            'unit_price'      => (float) $this->unit_price,
            'discount_amount' => (float) $this->discount_amount,
            'subtotal'        => (float) $this->subtotal,
        ];
    }
}
