<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmartListListResource extends JsonResource
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
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'image' => $this->image,
            'notify_on_price_drop' => $this->notify_on_price_drop,
            'notify_on_offers' => $this->notify_on_offers,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
