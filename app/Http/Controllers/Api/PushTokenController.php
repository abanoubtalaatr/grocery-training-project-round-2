<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushTokenController extends Controller
{
    /**
     * Register a push token for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['sometimes', 'string', 'in:web,ios,android'],
        ]);

        $user = $request->user();

        $pushToken = PushToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $validated['token'],
            ],
            [
                'platform' => $validated['platform'] ?? 'web',
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Push token registered successfully',
            'data' => [
                'id' => $pushToken->id,
                'platform' => $pushToken->platform,
            ],
        ], 201);
    }

    /**
     * Remove a push token for the authenticated user.
     */
    public function destroy(Request $request, string $token): JsonResponse
    {
        $user = $request->user();

        $deleted = PushToken::where('user_id', $user->id)
            ->where('token', $token)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Push token not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Push token removed successfully',
        ]);
    }

    /**
     * List push tokens for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->pushTokens()
            ->select('id', 'platform', 'last_used_at', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ]);
    }
}
