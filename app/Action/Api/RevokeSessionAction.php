<?php

namespace App\Action\Api;

class RevokeSessionAction
{
    public function execute($user, string $tokenId): bool
    {
        $currentTokenId = $user->currentAccessToken()?->id;

        if ((string) $tokenId === (string) $currentTokenId) {
            return false;
        }

        $token = $user->tokens()->find($tokenId);
        if (! $token) {
            return false;
        }

        $token->delete();

        return true;
    }
}
