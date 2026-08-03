<?php

namespace App\Http\Requests\Api;

use App\DTOs\Api\CategoryMealsData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryMealsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        return [
            'featured' => ['sometimes', 'boolean'],
            'subcategory_id' => ['sometimes', 'integer', Rule::exists('subcategories', 'id')->where('category_id', $category->id)],
            'in_stock' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', Rule::in(['newest', 'created_at', 'price', 'rating', 'title', 'sold_count'])],
            'sort_order' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
    public function toDto(): CategoryMealsData
    {
        return CategoryMealsData::fromValidated($this->validated());
    }
}
