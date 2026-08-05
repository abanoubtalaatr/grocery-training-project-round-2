<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Keep original RegisterRequest rules minimal here; complex rules exist in RegisterRequest
        return [
            'username' => ['required', 'string', 'min:3', 'max:32'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'agree_terms' => ['required', 'accepted'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
        ];
    }
}
