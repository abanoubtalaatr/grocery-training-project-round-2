<?php

namespace App\Policies;

use App\Models\SmartList;
use App\Models\User;

class SmartListPolicy
{
    public function view(User $user, SmartList $smartList): bool
    {
        return $smartList->user_id === $user->id || (bool) $user->is_admin;
    }

    public function update(User $user, SmartList $smartList): bool
    {
        return $this->view($user, $smartList);
    }

    public function delete(User $user, SmartList $smartList): bool
    {
        return $this->view($user, $smartList);
    }
}
