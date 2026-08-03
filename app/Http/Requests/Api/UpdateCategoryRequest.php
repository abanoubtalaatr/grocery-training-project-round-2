<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('id') ?? $this->route('category');

        return [
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'description' => 'nullable|string',
            'image_url'   => 'nullable|url',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ];
    }

    /**
     * Custom message for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'اسم القسم مطلوب عند إرساله.',
            'name.string'       => 'اسم القسم يجب أن يكون نصاً.',
            'name.max'          => 'اسم القسم يجب ألا يتجاوز 255 حرفاً.',

            'slug.string'       => 'الرابط الدائم (slug) يجب أن يكون نصاً.',
            'slug.max'          => 'الرابط الدائم يجب ألا يتجاوز 255 حرفاً.',
            'slug.unique'       => 'هذا القسم موجود مسبقاً! (الرابط المستخرج أو الممرر مستخدم لقسم آخر).',

            'image_url.url'     => 'رابط الصورة يجب أن يكون رابطاً إلكترونياً صحيحاً.',
            'sort_order.integer'=> 'ترتيب العرض يجب أن يكون رقماً صحيحاً.',
            'is_active.boolean' => 'حالة التفعيل يجب أن تكون قيمة منطقية (true/false).',
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name'        => 'اسم القسم',
            'slug'        => 'الرابط الدائم (slug)',
            'description' => 'الوصف',
            'image_url'   => 'رابط الصورة',
            'sort_order'  => 'ترتيب العرض',
            'is_active'   => 'حالة التفعيل',
        ];
    }
}