<?php

namespace App\Http\Controllers\Api\Sanctum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Phase 2 — Sanctum Personal Access Tokens (Flutter / SPA).
 */
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'auth' => 'sanctum',
            ], 422);
        }

        $token = $user->createSanctumToken('mobile');

        return response()->json([
            'message' => 'Logged in with Sanctum personal access token.',
            'auth' => 'sanctum',
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value,
            ],
            'demo' => [
                'stored_in' => 'personal_access_tokens',
                'client_sends' => 'Authorization: Bearer TOKEN',
                'example' => '$user->createSanctumToken(\'mobile\')->plainTextToken',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentSanctumToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Sanctum token revoked.',
            'auth' => 'sanctum',
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'auth' => 'sanctum',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value,
            ],
            'token' => [
                'name' => $user->currentSanctumToken()?->name,
                'abilities' => $user->currentSanctumToken()?->abilities,
            ],
        ]);
    }
}
