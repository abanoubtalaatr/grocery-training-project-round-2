<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category !== null && ($this->user()?->can('update', $category) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', 'unique:categories,name,'.$this->id],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:categories,slug,'.$this->id],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'image_url' => ['sometimes', 'nullable', 'url'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
