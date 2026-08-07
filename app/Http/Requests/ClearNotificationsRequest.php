<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClearNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'in:read,unread,all'],
            'confirmation' => ['required', 'accepted'],
        ];
    }
}