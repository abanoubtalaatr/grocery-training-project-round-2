<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
<<<<<<< HEAD
                'label' => ['sometimes', 'string', 'max:255'],
                'full_name' => ['sometimes', 'string', 'min:2', 'max:255'],
                'phone' => ['sometimes', 'string', 'min:10', 'max:20', 'regex:/^\+?[1-9]\d{9,14}$/'],
                'country_code' => ['sometimes', 'nullable', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
                'street_address' => ['sometimes', 'string', 'min:5', 'max:500'],
=======
            'label' => ['nullable', 'string', 'max:255'],
                'full_name' => ['required', 'string', 'min:2', 'max:255'],
                'phone' => ['required', 'string', 'min:10', 'max:20', 'regex:/^\+?[1-9]\d{9,14}$/'],
                'country_code' => ['nullable', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
                'street_address' => ['required', 'string', 'min:5', 'max:500'],
>>>>>>> origin/main
                'building_number' => ['nullable', 'string', 'max:50'],
                'floor' => ['nullable', 'string', 'max:50'],
                'apartment' => ['nullable', 'string', 'max:50'],
                'landmark' => ['nullable', 'string', 'max:255'],
<<<<<<< HEAD
                'city' => ['sometimes', 'string', 'min:2', 'max:100'],
=======
                'city' => ['required', 'string', 'min:2', 'max:100'],
>>>>>>> origin/main
                'state' => ['nullable', 'string', 'max:100'],
                'postal_code' => ['nullable', 'string', 'max:20'],
                'country' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'is_default' => ['nullable', 'boolean'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
<<<<<<< HEAD

    
=======
>>>>>>> origin/main
}
