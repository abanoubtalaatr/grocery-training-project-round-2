<?php

namespace App\Actions\Api\Profile;

use App\Models\User;

class DestroyProfileSessionAction
{
    public function run(User $user, string $tokenId, ?string $currentTokenId = null): bool
    {
        if ((string) $tokenId === (string) $currentTokenId) {
            throw new \InvalidArgumentException('Cannot revoke your current session from this request. Use logout instead.');
        }

        $token = $user->tokens()->find($tokenId);
        if (!$token) {
            return false;
        }

        $token->delete();
        return true;
    }
}
