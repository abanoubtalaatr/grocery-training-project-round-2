<?php

namespace App\Http\Requests\Api;

use App\Support\EmailValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', ...EmailValidation::formatRules(), 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.not_regex' => EmailValidation::trailingHyphenDotBeforeAtMessage(),
            'email.regex' => EmailValidation::domainStructureMessage(),
            'email.max' => 'The email address may not exceed 255 characters.',
        ];
    }
}
