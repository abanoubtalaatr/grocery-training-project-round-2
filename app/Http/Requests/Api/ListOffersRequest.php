<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListOffersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string'],
            'min_purchase' => ['sometimes', 'numeric', 'min:0'],
            'featured' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'string'],
            'order_by' => ['sometimes', 'string'],
            'order_direction' => ['sometimes', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
