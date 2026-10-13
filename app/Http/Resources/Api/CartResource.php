<?php

namespace App\Http\Resources\Api;

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
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'item_count' => (int) $this->item_count,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'coupon_code' => $this->coupon_code,
            'coupon_discount' => (float) ($this->coupon_discount ?? 0),
            'total' => (float) $this->total,
            'is_empty' => $this->isEmpty(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
