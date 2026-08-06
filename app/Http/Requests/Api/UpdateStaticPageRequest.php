<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaticPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pageId = $this->route('staticPage')?->id ?? $this->route('static_page');

        return [
            'slug' => 'sometimes|required|string|max:100|unique:static_pages,slug,' . $pageId,
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'sometimes|boolean',
            'order' => 'nullable|integer',
        ];
    }
}
