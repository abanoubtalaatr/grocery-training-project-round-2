<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'birthday' => $this->birthday?->format('Y-m-d'),
            'email_verified' => !is_null($this->email_verified_at),
            'phone_verified' => $this->phone_verified,
            'is_active' => !is_null($this->email_verified_at),
            'profile_image_url' => $this->profile_image_url,
            'addresses_count' => $this->whenCounted('addresses'),
            'orders_count' => $this->whenCounted('orders'),
            'addresses' => $this->whenLoaded('addresses'),
            'favorites' => $this->whenLoaded('favorites'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
