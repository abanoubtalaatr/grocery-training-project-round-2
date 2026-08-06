<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AuthUserResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request): JsonResponse
    {
        return $this->dataResponse([
            'user' => new AuthUserResource($request->user()),
        ], 'User details fetched successfully');
    }
}
