<?php

namespace App\Http\Requests\Api;

class StoreStaticPageRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'unique:static_pages,slug', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'array'],
            'is_published' => ['boolean'],
            'order' => ['nullable', 'integer'],
        ];
    }
}
