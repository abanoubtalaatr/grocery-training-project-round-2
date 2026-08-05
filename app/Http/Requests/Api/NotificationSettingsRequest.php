<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class NotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'boolean'],
            'push' => ['sometimes', 'boolean'],
            'sms' => ['sometimes', 'boolean'],
        ];
    }
}
