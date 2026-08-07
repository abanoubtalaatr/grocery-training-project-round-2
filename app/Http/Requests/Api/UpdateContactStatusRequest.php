<?php

namespace App\Http\Requests\Api;

class UpdateContactStatusRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:read,replied,spam'],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
