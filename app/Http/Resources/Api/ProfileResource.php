<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'me' => [
                'id' => $data['me']['id'],
                'profile_picture' => $data['me']['profile_picture'],
                'name' => $data['me']['name'],
                'username' => $data['me']['username'],
                'firstname' => $data['me']['firstname'],
                'lastname' => $data['me']['lastname'],
                'gender' => $data['me']['gender'],
                'birthday' => $data['me']['birthday'],
                'email' => $data['me']['email'],
                'phone' => $data['me']['phone'],
                'country_code' => $data['me']['country_code'],
                'email_verified' => $data['me']['email_verified'],
                'phone_verified' => $data['me']['phone_verified'],
                'preferred_languages' => $data['me']['preferred_languages'] ?? [],
                'created_at' => $data['me']['created_at'],
                'updated_at' => $data['me']['updated_at'],
            ],
            'addresses' => $data['addresses'] ?? [],
            'order_history' => $data['order_history'] ?? [],
            'in_progress_orders' => $data['in_progress_orders'] ?? [],
            'order_notifications' => $data['order_notifications'] ?? [],
            'settings' => $data['settings'] ?? [],
            'wishlist' => $data['wishlist'] ?? [],
        ];
    }
}