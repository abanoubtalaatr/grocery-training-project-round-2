<?php

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Support\Arr;

class UserUpdateAction
{
    public function execute(User $user, array $data): User
    {
        $user->update(Arr::except($data, []));

        return $user->refresh();
    }
}
