<?php

namespace App\Action\Admin\User;

use App\Models\User;

class DeleteUserAction
{
    public function execute(User $user): void
    {
        $user->tokens()->delete();
        $user->favorites()->delete();
        $user->addresses()->delete();
        $user->delete();
    }
}
