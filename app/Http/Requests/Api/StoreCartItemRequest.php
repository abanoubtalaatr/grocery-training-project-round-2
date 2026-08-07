<?php

namespace App\Http\Requests\Api;

use App\Models\Cart;
use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
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
            'meal_id' => ['required', 'exists:meals'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . Cart::MAX_QUANTITY_PER_PRODUCT]
        ];
    }
}
