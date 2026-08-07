<?php

namespace App\Http\Requests\Api;

class AddCartItemRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        return [
            'meal_id' => ['required', 'exists:meals,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $maxPerProduct],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.max' => 'Maximum ' . config('cart.max_quantity_per_product', 10) . ' units per product allowed.',
        ];
    }
}
