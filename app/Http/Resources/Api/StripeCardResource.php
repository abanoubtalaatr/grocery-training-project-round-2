<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StripeCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'brand' => $this->resource['card']['brand'] ?? null,
            'last4' => $this->resource['card']['last4'] ?? null,
            'exp_month' => $this->resource['card']['exp_month'] ?? null,
            'exp_year' => $this->resource['card']['exp_year'] ?? null,
            'is_default' => $this->resource['metadata']['is_default'] ?? false,
            'created_at' => $this->resource['created'] ?? null,
        ];
    }
}   