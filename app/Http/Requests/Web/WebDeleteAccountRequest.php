<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class WebDeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => trans('auth.validation.password.required'),
        ];
    }
}
