<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Meal::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_hot' => 'boolean',
            'rating' => 'nullable|numeric|min:0|max:5',
            'rating_count' => 'nullable|integer|min:0',
            'size' => 'nullable|string',
            'brand' => 'nullable|string',
            'offer_title' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'includes' => 'nullable|string',
            'how_to_use' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'available_date' => 'nullable|date',
            'sold_count' => 'nullable|integer|min:0',
        ];
    }
}
