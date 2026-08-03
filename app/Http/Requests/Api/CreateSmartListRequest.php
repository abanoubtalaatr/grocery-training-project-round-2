<?php

namespace App\Http\Requests\Api;

use App\DTOs\Api\CreateSmartListData;
use App\Models\SmartList;
use Illuminate\Foundation\Http\FormRequest;

class CreateSmartListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'category'             => ['nullable', 'string', 'max:100'],
            'description'          => ['nullable', 'string', 'max:255'],
            'image'                => ['nullable', 'image', 'max:2048'],
            'notify_on_price_drop' => ['sometimes', 'boolean'],
            'notify_on_offers'     => ['sometimes', 'boolean'],
            'meal_ids'             => ['sometimes', 'array'],
            'meal_ids.*'           => ['required', 'integer','distinct', 'exists:meals,id'],
        ];
    }

    public function toDto(): CreateSmartListData
    {
        return CreateSmartListData::fromValidated($this->validated());
    }
}