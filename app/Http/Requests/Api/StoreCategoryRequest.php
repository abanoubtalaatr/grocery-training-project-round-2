<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCategoryRequest extends FormRequest
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
        return [
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'image_url'   => 'nullable|url',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'يرجى إدخال اسم القسم، فهذا الحقل مطلوب.',
            'name.string'       => 'اسم القسم يجب أن يكون نصاً.',
            'name.max'          => 'اسم القسم يجب ألا يتجاوز 255 حرفاً.',

            'slug.required'     => 'الرابط الدائم (slug) مطلوب.',
            'slug.string'       => 'الرابط الدائم (slug) يجب أن يكون نصاً.',
            'slug.max'          => 'الرابط الدائم يجب ألا يتجاوز 255 حرفاً.',
            'slug.unique'       => 'هذا القسم موجود مسبقاً! (الرابط المستخرج من اسم القسم مكرر في قاعدة البيانات).',

            'image_url.url'     => 'رابط الصورة يجب أن يكون رابطاً إلكترونياً صحيحاً (مثال: https://example.com/image.jpg).',

            'sort_order.integer'=> 'ترتيب العرض يجب أن يكون رقماً صحيحاً.',

            'is_active.boolean' => 'حالة التفعيل يجب أن تكون إما متوفر (true) أو غير متوفر (false).',
        ];
    }

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