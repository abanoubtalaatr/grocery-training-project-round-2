<?php

namespace App\Action\Profile;

use App\Models\User;

class UpdateProfileAction
{
    public function handle(User $user, array $data): User
    {
        // Normalize phone if present
        if (isset($data['phone']) && is_string($data['phone'])) {
            $data['phone'] = preg_replace('/\s+/', '', $data['phone']);
        }

        // Ensure preferred_languages is present if sent
        if (array_key_exists('preferred_languages', $data) && $data['preferred_languages'] === null) {
            $data['preferred_languages'] = [];
        }

        // Remove null/empty values except preferred_languages
        $filtered = array_filter($data, function ($value, $key) {
            if ($key === 'preferred_languages') {
                return true;
            }

            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $user->update($filtered);

        return $user;
    }
}
