<?php

namespace App\Policies;

use App\Models\SmartList;
use App\Models\User;

class SmartListPolicy
{
    /**
     * Determine whether the user can view, update, or delete the smart list.
     */
    public function manage(User $user, SmartList $smartList): bool
    {
        return $user->id === $smartList->user_id;
    }
}