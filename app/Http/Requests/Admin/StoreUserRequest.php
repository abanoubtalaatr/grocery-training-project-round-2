<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\User::class);
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'birthday' => 'nullable|date|before:today',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'password' => 'nullable|string|min:8',
            'loyalty_points' => 'nullable|integer|min:0',
            'store_credits' => 'nullable|numeric|min:0',
        ];
    }
}
