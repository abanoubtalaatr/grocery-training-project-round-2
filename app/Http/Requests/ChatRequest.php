<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('message') && ! $this->filled('question')) {
            $this->merge([
                'question' => $this->input('message'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:1000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'locale' => ['nullable', 'string', 'in:ar,en'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Question is required.',
            'question.string' => 'Question must be text.',
            'question.max' => 'Question must not exceed 1000 characters.',
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => 'Minimum rating is 1.',
            'rating.max' => 'Maximum rating is 5.',
            'locale.in' => 'Locale must be ar or en.',
        ];
    }
}