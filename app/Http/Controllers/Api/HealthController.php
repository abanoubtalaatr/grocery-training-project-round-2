<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return $this->successResponse(null, 'API is running', 200, [
            'timestamp' => now(),
        ]);
    }
}
