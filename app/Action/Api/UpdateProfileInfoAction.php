<?php

namespace App\Action\Api;

use Illuminate\Validation\ValidationException;

class UpdateProfileInfoAction
{
    public function execute($user, array $data)
    {
        $data = array_filter($data, function ($value, $key) {
            if ($key === 'preferred_languages') {
                return true;
            }
            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($data)) {
            throw ValidationException::withMessages([
                'profile' => ['No data provided to update'],
            ]);
        }

        $user->update($data);

        return $user->fresh();
    }
}