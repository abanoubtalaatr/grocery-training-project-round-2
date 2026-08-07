<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
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
        return [
                'meal_id' => ['required', 'exists:meals,id'],
                'quantity' => ['required', 'integer', 'min:1', 'max:' . config('cart.max_quantity_per_product', 10)],
        ];
    }

        public function messages(): array
    {
        return [
                'quantity.max' => "Maximum". config('cart.max_quantity_per_product', 10) . "units per product allowed.",
        ];
    }



}
