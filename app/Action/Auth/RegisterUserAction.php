<?php

namespace App\Action\Auth;

use App\Models\User;
use Illuminate\Support\Str;

class RegisterUserAction
{
    public function handle(array $data): array
    {
        // Normalize phone
        if (isset($data['phone']) && is_string($data['phone'])) {
            $data['phone'] = preg_replace('/\s+/', '', $data['phone']);
        }

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'firstname' => $data['firstname'] ?? null,
            'lastname' => $data['lastname'] ?? null,
            'password' => $data['password'], // User model should hash
            'agree_terms' => $data['agree_terms'] ?? false,
            'email_verified' => false,
            'phone_verified' => false,
            'api_token' => Str::random(60),
        ]);

        // Optionally: create token using sanctum or passport
        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
