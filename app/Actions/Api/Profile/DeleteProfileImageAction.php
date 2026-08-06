<?php

namespace App\Actions\Api\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeleteProfileImageAction
{
    public function run(User $user): bool
    {
        if (!$user->profile_image) {
            return false;
        }

        // Delete image from storage
        if (Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Update user
        $user->update(['profile_image' => null]);

        return true;
    }
}
