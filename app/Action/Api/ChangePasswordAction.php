<?php

namespace App\Action\Api;

class ChangePasswordAction
{
    public function execute($user, string $password): void
    {
        // The User model has hashed cast for password
        $user->update(['password' => $password]);
    }
}
