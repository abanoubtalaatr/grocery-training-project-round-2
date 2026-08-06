<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization should be handled by policies or middleware where appropriate
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['sometimes', 'string', 'max:255'],
            'site_description' => ['sometimes', 'string', 'max:1000'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'logo' => ['sometimes', 'file', 'image', 'max:10240'],
            'favicon' => ['sometimes', 'file', 'image', 'max:2048'],
        ];
    }
}
