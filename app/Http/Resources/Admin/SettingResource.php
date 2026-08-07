<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_name' => $this->site_name,
            'site_description' => $this->site_description,
            'logo' => $this->logo,
            'logo_url' => $this->logo ? asset('storage/' . $this->logo) : null,
            'favicon' => $this->favicon,
            'favicon_url' => $this->favicon ? asset('storage/' . $this->favicon) : null,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'social_media' => [
                'facebook' => $this->facebook,
                'linkedin' => $this->linkedin,
                'instagram' => $this->instagram,
                'twitter' => $this->twitter,
            ],
            'copyright_text' => $this->copyright_text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
