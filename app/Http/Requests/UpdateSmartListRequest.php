<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSmartListRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Enforce ownership directly inside the Form Request!
        $smartList = $this->route('smartList');
        return $smartList && $smartList->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'meal_ids'    => ['nullable', 'array'],
            'meal_ids.*'  => ['required', 'integer', 'exists:meals,id'],
        ];
    }
}