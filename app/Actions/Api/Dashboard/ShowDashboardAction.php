<?php

namespace App\Actions\Api\Dashboard;

use Illuminate\Http\JsonResponse;

class ShowDashboardAction
{
    public function handle($id): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }
}
