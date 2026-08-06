<?php

namespace App\Action\Api;

class ListSessionsAction
{
    public function execute($user): array
    {
        $currentTokenId = $user->currentAccessToken()?->id;

        return $user->tokens()->get()->map(function ($token) use ($currentTokenId) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'is_current' => (string) $token->id === (string) $currentTokenId,
                'created_at' => $token->created_at?->toIso8601String(),
            ];
        })->values()->all();
    }
}
