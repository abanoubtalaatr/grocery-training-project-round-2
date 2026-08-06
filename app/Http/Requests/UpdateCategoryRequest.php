<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => ['sometimes','string','max:255'],

            'description' => ['nullable','string'],

            'image' => ['nullable','string'],

            'is_active' => ['boolean'],

            'sort_order' => ['nullable','integer']
        ];
    }
}