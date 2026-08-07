<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Profile\DestroyProfileSessionAction;
use App\Actions\Api\Profile\GetProfileSessionsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SessionResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    use ApiTrait;

    public function index(Request $request, GetProfileSessionsAction $action): JsonResponse
    {
        return $this->dataResponse(
            SessionResource::collection($action->run($request->user())),
            'Sessions retrieved successfully'
        );
    }

    public function destroy(Request $request, string $tokenId, DestroyProfileSessionAction $action): JsonResponse
    {
            $deleted = $action->run($request->user(), $tokenId, $request->user()->currentAccessToken()?->id);
            
            if (!$deleted) {
                return $this->errorResponse([], 'Session not found', 404);
            }

            return $this->successResponse('Session revoked successfully');

    }
}
