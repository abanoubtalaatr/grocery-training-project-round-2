<?php

namespace App\Action\Api;

use Illuminate\Validation\ValidationException;

class RevokeSessionAction
{
    public function execute($user, string $tokenId, $currentTokenId): void
    {
        if ((string) $tokenId === (string) $currentTokenId) {
            throw ValidationException::withMessages([
                'session' => ['Cannot revoke your current session from this request. Use logout instead.'],
            ]);
        }

        $token = $user->tokens()->find($tokenId);

        if (!$token) {
            throw ValidationException::withMessages([
                'session' => ['Session not found'],
            ]);
        }

        $token->delete();
    }
}