<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserActivityController extends Controller
{
    /**
     * Track user activity (session start or update last activity).
     */
    public function track(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Find existing activity from today or create new
            $activity = UserActivity::where('user_id', $user->id)
                ->whereDate('session_start', today())
                ->first();

            if ($activity) {
                // Update last activity
                $activity->update(['last_activity_at' => now()]);
            } else {
                // Create new activity session
                UserActivity::create([
                    'user_id' => $user->id,
                    'session_start' => now(),
                    'last_activity_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Activity tracked',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track activity', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to track activity',
                'error' => 'Internal server error',
            ], 500);
        }
    }
}
