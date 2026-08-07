<?php

namespace App\Actions\Admin\User;

use App\Models\User;

class ToggleAdminUserAction
{
    public function run(User $user): bool
    {
        $user->update(['is_admin' => !$user->is_admin]);

        return $user->is_admin;
    }
}
