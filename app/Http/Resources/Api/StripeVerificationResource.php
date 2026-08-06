<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StripeVerificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->resource['order_id'],
            'order_number' => $this->resource['order_number'],
            'status' => $this->resource['status'],
        ];
    }
}