<?php

namespace App\Action\Profile;

class DestroySessionAction
{
    public function handle($user, string $tokenId): void
    {
        $current = $user->currentAccessToken()?->id;
        if ((string) $tokenId === (string) $current) {
            abort(400, 'Cannot revoke your current session from this request. Use logout instead.');
        }

        $token = $user->tokens()->find($tokenId);
        if (! $token) {
            abort(404, 'Session not found');
        }

        $token->delete();
    }
}
