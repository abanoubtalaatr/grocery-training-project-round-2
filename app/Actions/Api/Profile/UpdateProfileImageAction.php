<?php

namespace App\Actions\Api\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfileImageAction
{
    public function run(User $user, UploadedFile $image): User
    {
        // Delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Store new image
        $path = $image->store('profile-images', 'public');

        // Update user
        $user->update(['profile_image' => $path]);

        return $user;
    }
}
