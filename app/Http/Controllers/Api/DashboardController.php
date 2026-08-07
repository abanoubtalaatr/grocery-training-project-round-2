<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Dashboard\GetDashboardDataAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Get dashboard statistics and insights
     */
    public function index(Request $request, GetDashboardDataAction $action): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $action->execute($user);

            return $this->success($data, 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve dashboard data', 500);
        }
    }

    /**
     * Get specific dashboard metric (not applicable - return 405)
     */
    public function show(Request $request): JsonResponse
    {
        return $this->error('Method Not Allowed', 405);
    }

    /**
     * Create dashboard data (not applicable - return 405)
     */
    public function store(Request $request): JsonResponse
    {
        return $this->error('Method Not Allowed', 405);
    }

    /**
     * Update dashboard data (not applicable - return 405)
     */
    public function update(Request $request): JsonResponse
    {
        return $this->error('Method Not Allowed', 405);
    }

    /**
     * Delete dashboard data (not applicable - return 405)
     */
    public function destroy(Request $request): JsonResponse
    {
        return $this->error('Method Not Allowed', 405);
    }
}
