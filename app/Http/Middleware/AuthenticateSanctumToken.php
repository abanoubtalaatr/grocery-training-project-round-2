<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase 2 helper: authenticate Sanctum PAT without Sanctum's HasApiTokens trait
 * (Passport already owns HasApiTokens on the User model).
 */
class AuthenticateSanctumToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! $bearer || ! str_contains($bearer, '|')) {
            return response()->json([
                'message' => 'Unauthenticated (Sanctum Bearer token required).',
                'auth' => 'sanctum',
            ], 401);
        }

        [$id, $plainTextToken] = explode('|', $bearer, 2);

        $accessToken = PersonalAccessToken::query()->find($id);

        if (
            ! $accessToken ||
            ! hash_equals($accessToken->token, hash('sha256', $plainTextToken)) ||
            ! $accessToken->tokenable instanceof User
        ) {
            return response()->json([
                'message' => 'Invalid Sanctum token.',
                'auth' => 'sanctum',
            ], 401);
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json([
                'message' => 'Sanctum token expired.',
                'auth' => 'sanctum',
            ], 401);
        }

        $user = $accessToken->tokenable->withSanctumAccessToken($accessToken);
        $accessToken->forceFill(['last_used_at' => now()])->save();

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
