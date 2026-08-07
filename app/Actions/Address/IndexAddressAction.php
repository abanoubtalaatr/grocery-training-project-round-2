<?php

namespace App\Actions\Address;

use App\Models\User;

class IndexAddressAction
{
    public function execute(User $user)
    {
        return $user->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}