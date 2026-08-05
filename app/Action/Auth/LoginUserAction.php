<?php

namespace App\Action\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginUserAction
{
    public function handle(string $login, string $password): array
    {
        $user = User::where('email', $login)
            ->orWhere('phone', preg_replace('/\s+/', '', $login))
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            abort(401, 'Invalid credentials');
        }

        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
