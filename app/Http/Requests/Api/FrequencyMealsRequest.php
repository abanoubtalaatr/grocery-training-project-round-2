<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FrequencyMealsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'frequency_type' => ['sometimes', 'string'],
            'subcategory_id' => ['sometimes', 'integer', 'exists:subcategories,id'],
        ];
    }
}
