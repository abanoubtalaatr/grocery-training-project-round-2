<?php

namespace App\Action\Api;

use Illuminate\Support\Facades\Storage;

class DeleteProfileImageAction
{
    public function execute($user): void
    {
        if (! $user->profile_image) {
            return;
        }

        if (Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->update(['profile_image' => null]);
    }
}
