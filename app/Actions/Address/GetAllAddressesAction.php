<?php

namespace App\Actions\Address;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetAllAddressesAction
{
    public function execute(User $user): Collection
    {
        return $user->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
