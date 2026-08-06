<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateCartItemRequest extends FormRequest
{
    use ApiTrait;

    protected function failedValidation(Validator $validator)
    {
        if ($this->is('api/*')) {
            $response = $this->errorResponse(
                $validator->errors(),
                'Validation error',
                422
            );

            throw new ValidationException($validator, $response);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . config('cart.max_quantity_per_product', 10),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Maximum ' . config('cart.max_quantity_per_product', 10) . ' units per product allowed.',
        ];
    }
}