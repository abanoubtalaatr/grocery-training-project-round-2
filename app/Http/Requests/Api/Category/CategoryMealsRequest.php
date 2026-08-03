<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryMealsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        return [
            'featured' => ['nullable', 'boolean'],

            'subcategory_id' => [
                'nullable',
                'integer',
                'exists:subcategories,id',
            ],

            'in_stock' => ['nullable', 'boolean'],

            'sort_by' => [
                'nullable',
                Rule::in([
                    'created_at',
                    'price',
                    'rating',
                    'title',
                    'sold_count',
                    'newest',
                ]),
            ],

            'sort_order' => [
                'nullable',
                Rule::in(['asc', 'desc']),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ];
    }
}