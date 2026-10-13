<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Email or phone number is required.',
            'password.required' => 'Password is required.',
        ];
    }
}
