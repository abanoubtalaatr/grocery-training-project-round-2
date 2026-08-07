<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'action_url' => ['nullable', 'string', 'max:500'],
            'action_label' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,urgent'],
        ];
    }
}
