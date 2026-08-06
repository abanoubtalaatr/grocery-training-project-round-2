<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'email_verified' => $this->email_verified ?? false,
            'phone_verified' => $this->phone_verified ?? false,
            'profile_image_url' => $this->profile_image_url ?? null,
            'created_at' => $this->created_at,
        ];
    }
}
