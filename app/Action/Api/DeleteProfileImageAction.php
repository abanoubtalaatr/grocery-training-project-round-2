<?php

namespace App\Action\Api;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteProfileImageAction
{
    public function execute($user): void
    {
        if (!$user->profile_image) {
            throw ValidationException::withMessages([
                'image' => ['No profile image to delete'],
            ]);
        }

        if (Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->update(['profile_image' => null]);
    }
}