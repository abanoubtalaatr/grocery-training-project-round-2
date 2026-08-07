<?php

namespace App\Action\Api;

use App\Models\User;

class ChangePasswordAction
{
    public function execute(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }
}