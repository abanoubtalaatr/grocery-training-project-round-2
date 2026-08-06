<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'order' => isset($data['order']) ? new OrderResource($data['order']) : null,
            'awaiting_payment' => $data['awaiting_payment'] ?? false,
            'tracking' => $data['tracking'] ?? null,
        ];
    }
}