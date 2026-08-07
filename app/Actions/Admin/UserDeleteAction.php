<?php

namespace App\Actions\Admin;

use App\Models\User;

class UserDeleteAction
{
    public function execute(User $user): void
    {
        $user->delete();
    }
}
