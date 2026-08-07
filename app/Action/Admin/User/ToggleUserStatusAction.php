<?php

namespace App\Action\Admin\User;

use App\Models\User;

class ToggleUserStatusAction
{
    public function execute(User $user): void
    {
        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
        } else {
            $user->update(['email_verified_at' => now()]);
        }
    }
}
