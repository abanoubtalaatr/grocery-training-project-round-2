<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled in controllers/policies if needed
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:1000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'locale' => ['nullable', 'string', 'in:ar,en'],
            'message' => ['sometimes'],
        ];
    }
}
