<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaticPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staticPageId = $this->route('staticPage')->id;

        return [
            'slug' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('static_pages', 'slug')->ignore($staticPageId)],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'array'],
            'is_published' => ['sometimes', 'boolean'],
            'order' => ['nullable', 'integer'],
        ];
    }
}
