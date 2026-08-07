<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'username'            => $this->username,
            'firstname'           => $this->firstname,
            'lastname'            => $this->lastname,
            'full_name'           => $this->full_name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'country_code'        => $this->country_code,
            'gender'              => $this->gender,
            'birthday'            => $this->birthday?->format('Y-m-d'),
            'profile_picture'     => $this->profile_image_url,
            'email_verified'      => $this->email_verified,
            'phone_verified'      => $this->phone_verified,
            'preferred_languages' => $this->preferred_languages ?? [],
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
