<?php

namespace App\Http\Requests\Api;

class AddSmartListMealRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'meal_id' => ['required', 'exists:meals,id'],
        ];
    }
}
