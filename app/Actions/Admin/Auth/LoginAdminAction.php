<?php

namespace App\Actions\Admin\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAdminAction
{
    public function run(array $credentials): bool
    {
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $attempt = Auth::attempt([
            $loginField => $credentials['login'],
            'password' => $credentials['password'],
        ], isset($credentials['remember']));

        if (!$attempt) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        $user = Auth::user();

        if (!$user->isAdmin()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => ['Unauthorized. Admin access required.'],
            ]);
        }

        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => ['Your account has been deactivated.'],
            ]);
        }

        return true;
    }
}
