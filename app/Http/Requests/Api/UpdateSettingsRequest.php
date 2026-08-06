<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['sometimes', 'string', 'max:255'],
            'site_description' => ['sometimes', 'string', 'max:1000'],
            'logo' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'favicon' => ['sometimes', 'image', 'mimes:ico,png', 'max:1024'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'address' => ['sometimes', 'string', 'max:500'],
            'facebook' => ['sometimes', 'nullable', 'url', 'max:255'],
            'linkedin' => ['sometimes', 'nullable', 'url', 'max:255'],
            'instagram' => ['sometimes', 'nullable', 'url', 'max:255'],
            'twitter' => ['sometimes', 'nullable', 'url', 'max:255'],
            'copyright_text' => ['sometimes', 'string', 'max:500'],
        ];
    }
}