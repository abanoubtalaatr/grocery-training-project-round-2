<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaticPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['sometimes', 'required', 'string', 'max:100', 'unique:static_pages,slug,'.$this->staticPage->id],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'meta_keywords' => ['sometimes', 'nullable', 'array'],
            'is_published' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
