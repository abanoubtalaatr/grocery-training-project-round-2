<?php

namespace App\Action\Api;

class RegisterAction
{
    public function execute(array $data): array
    {
        $user = \App\Models\User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}