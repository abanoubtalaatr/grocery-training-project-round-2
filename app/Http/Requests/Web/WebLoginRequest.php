<?php

namespace App\Http\Requests\Web;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class WebLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('login') || ! is_string($this->input('login'))) {
            return;
        }

        $login = trim($this->input('login'));
        if ($login !== '' && ! filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $login = preg_replace('/\s+/', '', $login);
        }

        $this->merge(['login' => $login]);
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => trans('auth.validation.login.required'),
            'password.required' => trans('auth.validation.password.required'),
        ];
    }
}
