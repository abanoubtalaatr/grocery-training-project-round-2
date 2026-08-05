<?php

namespace App\Action\Profile;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UpdateProfileImageAction
{
    public function handle(User $user, UploadedFile $image): User
    {
        // Delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $image->store('profile-images', 'public');

        $user->update(['profile_image' => $path]);

        return $user;
    }
}
