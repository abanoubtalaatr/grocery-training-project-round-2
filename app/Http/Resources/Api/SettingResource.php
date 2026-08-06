<?php

namespace App\Http\Resources\Api;

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
            'favicon' => $this->favicon,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'facebook' => $this->facebook,
            'linkedin' => $this->linkedin,
            'instagram' => $this->instagram,
            'twitter' => $this->twitter,
            'copyright_text' => $this->copyright_text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}