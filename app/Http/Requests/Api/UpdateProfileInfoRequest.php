<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Rules\UsernameMustContainLetter;
use App\Support\EgyptianPhoneRules;
use App\Support\EmailValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileInfoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->user()->id ?? null;

        return [
            'username' => ['sometimes', 'string', 'max:'.User::USERNAME_MAX_LENGTH, Rule::unique('users')->ignore($userId), 'not_regex:/\s/u', 'alpha_dash', new UsernameMustContainLetter],
            'firstname' => ['sometimes', 'string', 'max:255'],
            'lastname' => ['sometimes', 'string', 'max:255'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:20', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'birthday' => ['sometimes', 'nullable', 'date', 'before:today'],
            'email' => ['sometimes', ...EmailValidation::formatRules(), 'max:255', Rule::unique('users')->ignore($userId)],
            'phone' => ['sometimes', 'string', EgyptianPhoneRules::internationalPrefixRule(), 'min:11', 'max:13', EgyptianPhoneRules::mobileRule(), Rule::unique('users')->ignore($userId)],
            'country_code' => ['sometimes', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
            'preferred_languages' => ['sometimes', 'array'],
            'preferred_languages.*' => ['string', 'max:10'],
        ];
    }

    public function messages()
    {
        return [
            'username.max' => 'Maximum '.User::USERNAME_MAX_LENGTH.' characters allowed.',
        ];
    }
}
