<?php

namespace App\Http\Requests\Api;

use App\DTOs\Api\UpdateSmartListData;
use App\Models\SmartList;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSmartListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['sometimes', 'string', 'max:255'],
            'category'             => ['sometimes', 'nullable', 'string', 'max:100'],
            'description'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'image'                => ['sometimes', 'nullable', 'image', 'max:2048'],
            'notify_on_price_drop' => ['sometimes', 'boolean'],
            'notify_on_offers'     => ['sometimes', 'boolean'],
            'meal_ids'             => ['sometimes', 'array'],
            'meal_ids.*'           => ['required', 'integer', 'distinct','exists:meals,id'],
        ];
    }

    public function toDto(): UpdateSmartListData
    {
        return UpdateSmartListData::fromValidated($this->validated());
    }
}
