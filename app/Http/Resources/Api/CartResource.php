<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    private ?float $shippingFee;
    private ?float $totalWithShipping;

    public function __construct(mixed $resource, ?float $shippingFee = null, ?float $totalWithShipping = null)
    {
        parent::__construct($resource);
        $this->shippingFee       = $shippingFee;
        $this->totalWithShipping = $totalWithShipping;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id'         => $this->id,
            'status'     => $this->isEmpty() ? 'empty' : 'not empty',
            'items'      => CartItemResource::collection($this->whenLoaded('items')),
            'item_count' => $this->item_count,
            'subtotal'   => (float) $this->subtotal,
            'tax'        => (float) $this->tax,
            'discount'   => (float) $this->discount,
            'total'      => (float) $this->total,
            'is_empty'   => $this->isEmpty(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->shippingFee !== null && $this->totalWithShipping !== null) {
            $data['shipping_fee']       = $this->shippingFee;
            $data['total_with_shipping'] = $this->totalWithShipping;
        }

        return $data;
    }
}
