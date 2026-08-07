<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'featured' => ['nullable', 'boolean'],
            'in_stock' => ['nullable', 'boolean'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_rating' => ['nullable', 'numeric', 'between:0,5'],
            'brand' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:created_at,price,rating,title,sold_count,newest'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc,ASC,DESC'],
        ];
    }
}