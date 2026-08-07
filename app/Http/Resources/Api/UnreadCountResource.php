<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnreadCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'count' => $this->resource['count'],
            'has_unread' => $this->resource['count'] > 0,
        ];
    }
}