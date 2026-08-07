<?php

namespace App\Actions\Admin;

use App\Models\User;

class UserShowAction
{
    public function execute(User $user): User
    {
        $user->load(['orders', 'addresses']);

        return $user;
    }
}
