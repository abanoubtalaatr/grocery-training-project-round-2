<?php

namespace App\Actions\Profile;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;

class DeleteProfileImageAction
{
    public function execute(User $user): User
    {
        if (! $user->profile_image) {
            throw new Exception('No profile image to delete');
        }

        if (Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->update(['profile_image' => null]);

        return $user->fresh();
    }
}
