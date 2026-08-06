<?php

namespace App\Actions\Api\Profile;

use App\Models\User;

class UpdateProfileInfoAction
{
    public function run(User $user, array $data): User
    {
        // Handle preferred_languages separately (can be empty array)
        if (array_key_exists('preferred_languages', $data)) {
            $data['preferred_languages'] = $data['preferred_languages'] ?? [];
        }

        // Remove empty values (except preferred_languages which can be empty array)
        $data = array_filter($data, function ($value, $key) {
            if ($key === 'preferred_languages') {
                return true; // Always include preferred_languages even if empty
            }

            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        if (!empty($data)) {
            $user->update($data);
        }

        return $user;
    }
}
