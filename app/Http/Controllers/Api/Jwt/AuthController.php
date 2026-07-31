<?php

namespace App\Http\Controllers\Api\Jwt;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Http\JsonResponse;

/**
 * Phase 4 — JWT for microservice-style APIs (stateless).
 */
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('jwt')->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'auth' => 'jwt',
            ], 422);
        }

        return $this->respondWithToken($token, 'Logged in with JWT.');
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('jwt')->refresh(), 'JWT refreshed.');
    }

    public function profile(): JsonResponse
    {
        $user = auth('jwt')->user();

        return response()->json([
            'auth' => 'jwt',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value,
            ],
            'demo' => [
                'stored' => 'Usually only on the client (stateless)',
                'server_lookup' => 'No personal_access_tokens / oauth_access_tokens row required for basic JWT',
                'client_sends' => 'Authorization: Bearer JWT',
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        auth('jwt')->logout();

        return response()->json([
            'message' => 'JWT invalidated (logged out).',
            'auth' => 'jwt',
        ]);
    }

    private function respondWithToken(string $token, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'auth' => 'jwt',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => auth('jwt')->factory()->getTTL() * 60,
            'demo' => [
                'package' => 'php-open-source-saver/jwt-auth',
                'flow' => 'Login → Generate JWT → Return JWT → Client stores JWT → Bearer JWT',
            ],
        ]);
    }
}
