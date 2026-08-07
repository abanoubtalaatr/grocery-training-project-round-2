<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetCategoryMealsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'featured' => ['nullable', 'boolean'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'in_stock' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'in:created_at,price,rating,title,sold_count,newest'],
            'sort_order' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}