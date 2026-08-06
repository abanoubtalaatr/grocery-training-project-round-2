<?php

namespace App\Action\Api;

class UpdateProfileInfoAction
{
    public function execute($user, array $data): array
    {
        // Normalize phone whitespace removal (as previous controller did)
        if (isset($data['phone']) && is_string($data['phone'])) {
            $data['phone'] = preg_replace('/\s+/', '', $data['phone']);
        }

        // Handle preferred_languages explicitly
        if (array_key_exists('preferred_languages', $data) && $data['preferred_languages'] === null) {
            $data['preferred_languages'] = [];
        }

        // Remove empty values except preferred_languages
        $filtered = array_filter($data, function ($value, $key) {
            if ($key === 'preferred_languages') return true;
            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($filtered)) {
            return ['updated' => false, 'data' => []];
        }

        $user->update($filtered);

        return ['updated' => true, 'data' => $user->fresh()->toArray()];
    }
}
