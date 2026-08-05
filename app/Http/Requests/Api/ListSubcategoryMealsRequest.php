<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListSubcategoryMealsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'featured' => ['sometimes', 'boolean'],
            'in_stock' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string'],
            'sort_order' => ['sometimes', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
