<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $maxPerProduct],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        
        return [
            'quantity.max' => "Maximum {$maxPerProduct} units per product allowed.",
        ];
    }
}
