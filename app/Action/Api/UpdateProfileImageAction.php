<?php

namespace App\Action\Api;

use Illuminate\Support\Facades\Storage;

class UpdateProfileImageAction
{
    public function execute($user, $uploadedFile): array
    {
        // Delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $uploadedFile->store('profile-images', 'public');

        $user->update(['profile_image' => $path]);

        return ['profile_image' => $user->profile_image, 'profile_image_url' => $user->profile_image_url];
    }
}
