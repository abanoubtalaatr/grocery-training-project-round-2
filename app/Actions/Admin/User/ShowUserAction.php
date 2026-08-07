<?php

namespace App\Actions\Admin\User;

use App\Models\User;

class ShowUserAction
{
    public function run(User $user): User
    {
        $user->load(['orders' => fn($q) => $q->orderByDesc('created_at')->take(5), 'favorites', 'addresses']);
        $user->loadCount(['orders', 'favorites']);

        return $user;
    }
}
