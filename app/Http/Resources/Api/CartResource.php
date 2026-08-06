<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,

            'status' => $this->isEmpty() ? 'empty' : 'not empty',

            'items' => CartItemResource::collection($this->items),

            'item_count' => $this->item_count,

            'subtotal' => (float) $this->subtotal,

            'tax' => (float) $this->tax,

            'discount' => (float) $this->discount,

            'total' => (float) $this->total,

            'is_empty' => $this->isEmpty(),

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];

        if (isset($this->shipping_fee)) {
            $data['shipping_fee'] = (float) $this->shipping_fee;
        }

        if (isset($this->total_with_shipping)) {
            $data['total_with_shipping'] = (float) $this->total_with_shipping;
        }

        return $data;
    }
}