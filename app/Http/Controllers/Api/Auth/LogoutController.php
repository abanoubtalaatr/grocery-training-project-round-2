<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\LogoutUserAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, LogoutUserAction $action): JsonResponse
    {
        try {
            $action->run($request->user());

            return $this->successResponse('Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Logout failed', 500);
        }
    }
}
