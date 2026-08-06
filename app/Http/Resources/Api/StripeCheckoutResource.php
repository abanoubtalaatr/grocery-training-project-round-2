<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StripeCheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'checkout_url' => $this->resource['checkout_url'],
            'session_id' => $this->resource['session_id'],
            'order_id' => $this->resource['order_id'],
        ];
    }
}