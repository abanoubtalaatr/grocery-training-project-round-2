<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ContactUpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controller will use policies
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:read,replied,spam'],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
