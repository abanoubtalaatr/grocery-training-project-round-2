<?php

namespace App\Action\Admin\User;

use App\Models\User;

class GetUserOrdersAction
{
    public function execute(User $user)
    {
        return $user->orders()->with(['items.meal'])->latest()->get();
    }
}
