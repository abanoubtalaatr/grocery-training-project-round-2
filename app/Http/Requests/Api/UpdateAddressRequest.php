<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label'           => ['sometimes', 'string', 'max:255'],
            'full_name'       => ['sometimes', 'string', 'min:2', 'max:255'],
            'phone'           => ['sometimes', 'string', 'min:10', 'max:20', 'regex:/^\+?[1-9]\d{9,14}$/'],
            'country_code'    => ['sometimes', 'nullable', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
            'street_address'  => ['sometimes', 'string', 'min:5', 'max:500'],
            'building_number' => ['nullable', 'string', 'max:50'],
            'floor'           => ['nullable', 'string', 'max:50'],
            'apartment'       => ['nullable', 'string', 'max:50'],
            'landmark'        => ['nullable', 'string', 'max:255'],
            'city'            => ['sometimes', 'string', 'min:2', 'max:100'],
            'state'           => ['nullable', 'string', 'max:100'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'country'         => ['nullable', 'string', 'max:100'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'is_default'      => ['nullable', 'boolean'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
