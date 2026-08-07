<?php

namespace App\Http\Requests\Api;

class UpdateReviewRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'images' => ['sometimes', 'nullable', 'array'],
            'images.*' => ['string', 'max:2048'],
        ];
    }
}
