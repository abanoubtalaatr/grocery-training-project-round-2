<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'meal_id' => $this['meal_id'],
            'is_favorited' => $this['is_favorited'],
        ];
    }
}