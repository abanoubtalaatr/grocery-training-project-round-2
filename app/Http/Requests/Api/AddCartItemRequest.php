<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $max = config('cart.max_quantity_per_product', 10);
        return [
            'meal_id' => ['required', 'exists:meals,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $max],
        ];
    }

    public function messages(): array
    {
        $max = config('cart.max_quantity_per_product', 10);
        return [
            'quantity.max' => "Maximum {$max} units per product allowed.",
        ];
    }
}
