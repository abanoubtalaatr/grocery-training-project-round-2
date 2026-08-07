<?php

namespace App\Http\Requests\Api;

class ValidateOfferRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
