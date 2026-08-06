<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'images' => ['sometimes', 'nullable', 'array'],
            'images.*' => ['string'],
        ];
    }
}
