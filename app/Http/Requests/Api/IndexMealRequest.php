<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'subcategory_id' => 'nullable|integer',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'brand' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'in_stock' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:created_at,price,rating,title,sold_count,newest',
            'sort_order' => 'nullable|string|in:asc,desc,ASC,DESC',
        ];
    }
}
