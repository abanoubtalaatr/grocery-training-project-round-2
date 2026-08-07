<?php

namespace App\Actions\Auth;

use App\Models\User;

class ChangePasswordAction
{
    public function execute(User $user, string $newPassword): void
    {
        $user->update([
            'password' => $newPassword,
        ]);
    }
}
