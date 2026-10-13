<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class WebForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => trans('auth.validation.identifier.required'),
        ];
    }
}
