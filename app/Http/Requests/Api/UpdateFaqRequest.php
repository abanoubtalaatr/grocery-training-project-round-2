<?php

namespace App\Http\Requests\Api;

class UpdateFaqRequest extends StoreFaqRequest
{
    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'required', 'string', 'max:255'],
            'answer' => ['sometimes', 'required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'order' => ['nullable', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
