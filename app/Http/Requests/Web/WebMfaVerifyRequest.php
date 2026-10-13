<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class WebMfaVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $otpLength = max(4, min(8, (int) config('otp.length', 4)));

        return [
            'otp' => ['required', 'string', 'digits:'.$otpLength],
        ];
    }

    public function messages(): array
    {
        $otpLength = max(4, min(8, (int) config('otp.length', 4)));

        return [
            'otp.required' => trans('auth.validation.otp.required'),
            'otp.digits' => trans('auth.validation.otp.size', ['size' => $otpLength]),
        ];
    }
}
