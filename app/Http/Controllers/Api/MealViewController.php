<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\MealView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MealViewController extends Controller
{
    /**
     * Track a meal view for authenticated user.
     */
    public function track(Request $request, Meal $meal): JsonResponse
    {
        try {
            $user = $request->user();

            MealView::create([
                'user_id' => $user->id,
                'meal_id' => $meal->id,
                'viewed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Meal view tracked',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track meal view', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to track view',
                'error' => 'Internal server error',
            ], 500);
        }
    }
}
