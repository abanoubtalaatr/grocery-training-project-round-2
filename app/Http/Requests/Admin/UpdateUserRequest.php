<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? null;

        return [
            'username' => 'required|string|max:50|unique:users,username,' . $userId,
            'email' => 'required|email|unique:users,email,' . $userId,
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'birthday' => 'nullable|date|before:today',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'email_verified' => 'boolean',
            'phone_verified' => 'boolean',
            'loyalty_points' => 'nullable|integer|min:0',
            'store_credits' => 'nullable|numeric|min:0',
        ];
    }
}
