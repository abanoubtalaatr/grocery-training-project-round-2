<?php

namespace App\Http\Requests\Admin\Meal;

use Illuminate\Foundation\Http\FormRequest;

class StoreMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'    => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'image_file'     => ['nullable', 'image', 'max:4096'],
            'image'          => ['nullable', 'url', 'max:500'],
            'offer_title'    => ['nullable', 'string', 'max:100'],
            'price'          => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'size'           => ['nullable', 'string', 'max:100'],
            'expiry_date'    => ['nullable', 'date'],
            'available_date' => ['nullable', 'date'],
            'includes'       => ['nullable', 'string'],
            'how_to_use'     => ['nullable', 'string'],
            'features'       => ['nullable', 'string'],
            'brand'          => ['nullable', 'string', 'max:100'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'is_featured'    => ['nullable', 'boolean'],
            'is_available'   => ['nullable', 'boolean'],
            'is_hot'         => ['nullable', 'boolean'],
        ];
    }
}
