<?php

namespace App\Actions\Api\Auth;

use App\Models\User;

class ChangePasswordAction
{
    public function run(User $user, string $newPassword): void
    {
        $user->update([
            'password' => $newPassword,
        ]);
    }
}
