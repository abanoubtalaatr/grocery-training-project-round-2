<?php

namespace App\Action\Profile;

class ListSessionsAction
{
    public function handle($user): array
    {
        $current = $user->currentAccessToken()?->id;

        $tokens = $user->tokens()->get()->map(function ($token) use ($current) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'is_current' => (string) $token->id === (string) $current,
                'created_at' => $token->created_at?->toIso8601String(),
            ];
        })->toArray();

        return $tokens;
    }
}
