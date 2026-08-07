<?php

namespace App\Actions\Admin\User;

use App\Models\User;

class ToggleActiveUserAction
{
    public function run(User $user): bool
    {
        $user->update(['is_active' => !$user->is_active]);

        return $user->is_active;
    }
}
