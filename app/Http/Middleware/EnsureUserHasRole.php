<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! $user->hasRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. This action requires one of the following roles: ' . implode(', ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
