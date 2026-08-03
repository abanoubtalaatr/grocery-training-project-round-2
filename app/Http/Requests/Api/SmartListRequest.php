<?php

namespace App\Http\Requests\Api;

use App\DTOs\Api\SmartListData;
use App\Models\SmartList;
use Illuminate\Foundation\Http\FormRequest;

class SmartListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $smartList = $this->route('smart_list');

        return $smartList
            ? $this->user()->can('update', $smartList)
            : $this->user()->can('create', SmartList::class);
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
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'notify_on_price_drop' => ['sometimes', 'boolean'],
            'notify_on_offers' => ['sometimes', 'boolean'],
            'meal_ids' => ['sometimes', 'array'],
            'meal_ids.*' => ['required', 'exists:meals,id'],
        ];
    }

    public function toDto(): SmartListData
    {
        return SmartListData::fromValidated($this->validated());
    }
}
