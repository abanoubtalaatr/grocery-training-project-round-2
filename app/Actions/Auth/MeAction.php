<?php

namespace App\Actions\Auth;

use App\Models\User;

class MeAction
{
    public function execute(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified' => $user->email_verified,
                'phone_verified' => $user->phone_verified,
                'created_at' => $user->created_at,
            ],
        ];
    }
}