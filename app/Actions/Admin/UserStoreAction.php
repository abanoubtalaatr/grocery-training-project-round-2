<?php

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UserStoreAction
{
    public function execute(array $data): User
    {
        // Ensure password exists for admin-created users
        if (empty($data['password'])) {
            $data['password'] = bcrypt(Str::random(12));
        }

        $user = User::create(Arr::except($data, []));

        return $user;
    }
}
