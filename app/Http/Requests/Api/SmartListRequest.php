<?php

namespace App\Http\Requests\Api;

class SmartListRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
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
