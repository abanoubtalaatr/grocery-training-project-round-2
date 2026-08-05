<?php

namespace App\Action\Profile;

use App\Models\User;

class DeleteProfileImageAction
{
    public function handle(User $user): User
    {
        if ($user->profile_image) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_image);
            }

            $user->update(['profile_image' => null]);
        }

        return $user;
    }
}
