<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class WebResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $otpLength = max(4, min(8, (int) config('otp.length', 4)));

        return [
            'identifier' => ['required', 'string'],
            'otp' => ['required', 'string', 'digits:'.$otpLength],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        $otpLength = max(4, min(8, (int) config('otp.length', 4)));

        return [
            'identifier.required' => trans('auth.validation.identifier.required'),
            'otp.required' => trans('auth.validation.otp.required'),
            'otp.digits' => trans('auth.validation.otp.size', ['size' => $otpLength]),
            'password.required' => trans('auth.validation.password.required'),
            'password.min' => trans('auth.validation.password.min'),
            'password.confirmed' => trans('auth.validation.password.confirmed'),
        ];
    }
}
