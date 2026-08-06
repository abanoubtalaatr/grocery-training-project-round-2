<?php

namespace App\Action\Api;

class LoginAction
{
    public function execute(array $data): array
    {
        $login = $data['login'];
        $password = $data['password'];

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        $user = \App\Models\User::where($field, $login)->first();

        if (!$user || !\Hash::check($password, $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'login' => ['Invalid credentials'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}