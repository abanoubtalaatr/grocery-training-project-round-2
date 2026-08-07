<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'status' => $this->isEmpty() ? 'empty' : 'not empty',
            'items' => $this->items->map(function ($item) {

            }),
            'item_count' => $this->item_count,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'is_empty' => $this->isEmpty(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
                    'id' => $this->id,
                    'meal' => [
                        'id' => $this->meal->id,
                        'title' => $this->meal->title,
                        'slug' => $this->meal->slug,
                        'image_url' => $this->meal->image_url,
                        ...$this->meal->getApiPriceAttributes(),
                        'rating' => (float) $this->meal->rating,
                        'size' => $this->meal->size,
                        'brand' => $this->meal->brand,
                        'stock_quantity' => $this->meal->stock_quantity,
                        'is_available' => $this->meal->is_available,
                        'in_stock' => $this->meal->isInStock(),
                        'category' => $this->meal->category ? [
                            'id' => $this->meal->category->id,
                            'name' => $this->meal->category->name,
                        ] : null,
                        'subcategory' => $this->meal->subcategory ? [
                            'id' => $this->meal->subcategory->id,
                            'name' => $this->meal->subcategory->name,
                        ] : null,
                    ],
                    'quantity' => $this->quantity,
                    'unit_price' => (float) $this->unit_price,
                    'discount_amount' => (float) $this->discount_amount,
                    'subtotal' => (float) $this->subtotal,
                ];
    }
}
