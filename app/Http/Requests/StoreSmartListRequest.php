<?php

namespace App\Http\Requests;

use App\Models\SmartList;
use Illuminate\Foundation\Http\FormRequest;

class StoreSmartListRequest extends FormRequest
{
    public function authorize(): bool
    {return $this->user()->can('create', SmartList::class);


    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2MB Max
            'meal_ids'    => ['nullable', 'array'],
            'meal_ids.*'  => ['required', 'integer', 'exists:meals,id'],
        ];
    }
}