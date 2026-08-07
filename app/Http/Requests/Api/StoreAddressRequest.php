<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
<<<<<<< HEAD
use Override;
=======
>>>>>>> origin/main

class StoreAddressRequest extends FormRequest
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
                            'label' => ['nullable', 'string', 'max:255'],
=======
            'label' => ['nullable', 'string', 'max:255'],
>>>>>>> origin/main
                'full_name' => ['required', 'string', 'min:2', 'max:255'],
                'phone' => ['required', 'string', 'min:10', 'max:20', 'regex:/^\+?[1-9]\d{9,14}$/'],
                'country_code' => ['nullable', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
                'street_address' => ['required', 'string', 'min:5', 'max:500'],
                'building_number' => ['nullable', 'string', 'max:50'],
                'floor' => ['nullable', 'string', 'max:50'],
                'apartment' => ['nullable', 'string', 'max:50'],
                'landmark' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'min:2', 'max:100'],
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

    public function messages(): array
    {
    return[
                'full_name.min' => 'Full name must be at least 2 characters.',
                'phone.min' => 'Phone number must be at least 10 digits.',
                'phone.regex' => 'Please enter a valid phone number (with country code if needed).',
                'street_address.min' => 'Street address must be at least 5 characters.',
                'city.min' => 'City must be at least 2 characters.',
    ];
        
    }
=======
>>>>>>> origin/main
}
