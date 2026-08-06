<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('message') && ! $this->filled('question')) {
            $this->merge(['question' => $this->input('message')]);
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach (['question', 'message'] as $key) {
                if ($this->hasFile($key)) {
                    $validator->errors()->add('question', 'The question must be a text value, not a file.');
                }
            }

            $rawQuestion = $this->input('question');
            if (is_array($rawQuestion)) {
                $validator->errors()->add('question', 'Multiple question values are not allowed.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'question.required' => 'A non-empty question is required.',
        ];
    }
}
