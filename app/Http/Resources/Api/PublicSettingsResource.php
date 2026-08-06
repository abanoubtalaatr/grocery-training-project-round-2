<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'site_name' => $this->site_name,
            'site_description' => $this->site_description,
            'social_media' => [
                'facebook' => $this->facebook,
                'linkedin' => $this->linkedin,
                'instagram' => $this->instagram,
                'twitter' => $this->twitter,
            ],
            'contact' => [
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
            ],
            'logo' => $this->logo,
            'copyright' => $this->copyright_text,
        ];
    }
}