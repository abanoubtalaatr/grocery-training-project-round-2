<?php

namespace App\Http\Requests\Api;

class StoreReviewRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'meal_id' => ['required', 'exists:meals,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'max:2048'],
        ];
    }
}
