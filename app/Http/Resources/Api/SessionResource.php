<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['name'],
            'last_used_at' => $this->resource['last_used_at'],
            'is_current' => $this->resource['is_current'],
            'created_at' => $this->resource['created_at'],
        ];
    }
}