<?php

namespace App\Actions\Api\Dashboard;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreDashboardAction
{
    public function handle(Request $request): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }
}
