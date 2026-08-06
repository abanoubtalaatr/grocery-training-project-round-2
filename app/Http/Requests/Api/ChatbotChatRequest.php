<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['question', 'message'] as $key) {
            if ($this->hasFile($key)) {
                abort(422, 'Send the question as plain text (form field or JSON), not as a file upload.');
            }
        }

        if ($this->filled('message') && !$this->filled('question')) {
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

    public function messages(): array
    {
        return [
            'question.required' => 'A non-empty question is required.',
            'question.max' => 'The question must not exceed 1000 characters.',
        ];
    }
}