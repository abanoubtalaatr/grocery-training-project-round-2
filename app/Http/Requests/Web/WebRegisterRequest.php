<?php

namespace App\Http\Requests\Web;

use App\Models\User;
use App\Rules\UsernameMustContainLetter;
use App\Support\EgyptianPhoneRules;
use App\Support\EmailValidation;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class WebRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone') && is_string($this->input('phone'))) {
            $this->merge(['phone' => preg_replace('/\s+/', '', $this->input('phone'))]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:'.User::USERNAME_MAX_LENGTH,
                'alpha_dash',
                new UsernameMustContainLetter,
                'min:3',
                'unique:users,username,NULL,id,deleted_at,NULL',
                function (string $_attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }
                    $trashed = User::onlyTrashed()->where('username', $value)->first();
                    if ($trashed && ! $trashed->is_active) {
                        $fail(trans('auth.validation.username.blocked'));
                    }
                },
            ],
            'email' => [
                'nullable',
                'required_without:phone',
                ...EmailValidation::formatRules(),
                'max:255',
                'unique:users,email,NULL,id,deleted_at,NULL',
                function (string $_attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }
                    $trashed = User::onlyTrashed()->whereRaw('LOWER(email) = ?', [strtolower(trim($value))])->first();
                    if ($trashed && ! $trashed->is_active) {
                        $fail(trans('auth.validation.email.blocked'));
                    }
                },
            ],
            'phone' => [
                'nullable',
                'string',
                'required_without:email',
                EgyptianPhoneRules::internationalPrefixRule(),
                'min:11',
                'max:13',
                EgyptianPhoneRules::mobileRule(),
                'unique:users,phone,NULL,id,deleted_at,NULL',
                function (string $_attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }
                    $normalized = preg_replace('/\s+/', '', $value);
                    $trashed = User::onlyTrashed()->where('phone', $normalized)->first();
                    if ($trashed && ! $trashed->is_active) {
                        $fail(trans('auth.validation.phone.blocked'));
                    }
                },
            ],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => trans('auth.validation.username.required'),
            'username.unique' => trans('auth.validation.username.unique'),
            'username.alpha_dash' => trans('auth.validation.username.alpha_dash'),
            'email.required_without' => trans('auth.validation.email.required_without'),
            'email.email' => trans('auth.validation.email.email'),
            'email.unique' => trans('auth.validation.email.unique'),
            'phone.required_without' => trans('auth.validation.phone.required_without'),
            'phone.unique' => trans('auth.validation.phone.unique'),
            'password.required' => trans('auth.validation.password.required'),
            'password.min' => trans('auth.validation.password.min'),
            'password.confirmed' => trans('auth.validation.password.confirmed'),
        ];
    }
}
