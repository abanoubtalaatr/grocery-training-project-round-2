<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'label' => $this->label,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'formatted_phone' => $this->formatted_phone,
            'street_address' => $this->street_address,
            'building_number' => $this->building_number,
            'floor' => $this->floor,
            'apartment' => $this->apartment,
            'landmark' => $this->landmark,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'notes' => $this->notes,
            'is_default' => $this->is_default,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'full_address' => $this->full_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
