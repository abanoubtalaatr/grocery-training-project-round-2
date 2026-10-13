<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplySupportMessageRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
