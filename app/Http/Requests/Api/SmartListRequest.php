<?php

namespace App\Http\Requests\Api;


use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ApiTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;


class SmartListRequest extends FormRequest
{
    use ApiTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->is('api/*')) {
            $response = $this->errorResponse($validator->errors(),"Validation error",422);
            throw new ValidationException($validator,$response);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:2048'],
            'notify_on_price_drop' => ['sometimes', 'boolean'],
            'notify_on_offers' => ['sometimes', 'boolean'],
            'meal_ids' => ['sometimes', 'array'],
            'meal_ids.*' => ['required', 'exists:meals,id'],
        ];
    }
}
