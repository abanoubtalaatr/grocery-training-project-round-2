<?php

namespace App\Action\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class LogoutUserAction
{
    public function handle(Authenticatable $user): void
    {
        // Revoke current token
        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }
    }
}
