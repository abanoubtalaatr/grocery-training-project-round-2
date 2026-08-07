<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('contactMessage')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:read,replied,spam'],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}