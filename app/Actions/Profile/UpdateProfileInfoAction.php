<?php

namespace App\Actions\Profile;

use App\Models\User;
use Exception;

class UpdateProfileInfoAction
{
    public function execute(User $user, array $validated): User
    {
        $data = array_intersect_key($validated, array_flip([
            'username', 'firstname', 'lastname', 'gender', 'birthday', 'email', 'phone', 'country_code', 'preferred_languages',
        ]));

        if (array_key_exists('preferred_languages', $validated)) {
            $data['preferred_languages'] = $validated['preferred_languages'] ?? [];
        }

        $data = array_filter($data, function ($value, $key) {
            if ($key === 'preferred_languages') {
                return true;
            }

            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($data)) {
            throw new Exception('No data provided to update');
        }

        $user->update($data);

        return $user->fresh();
    }
}
