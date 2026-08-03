<?php

namespace App\Policies;

use App\Models\SmartListList;
use App\Models\User;

class SmartListListPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SmartListList $smartListList): bool
    {
        return $smartListList->user_id == $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SmartListList $smartListList): bool
    {
        return $smartListList->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SmartListList $smartListList): bool
    {
        return $smartListList->user_id == $user->id;
    }
}
