<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->when($this->relationLoaded('user'), [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
                'username' => $this->user->username,
            ]),
            'meal' => $this->when($this->relationLoaded('meal'), [
                'id' => $this->meal->id,
                'title' => $this->meal->title,
                'slug' => $this->meal->slug,
            ]),
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'images' => $this->images ?? [],
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
