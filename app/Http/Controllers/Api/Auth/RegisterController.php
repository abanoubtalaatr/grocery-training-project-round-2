<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\Api\AuthUserResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    use ApiTrait;

    public function __invoke(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        try {
            $result = $action->run($request->validated());

            return $this->dataResponse([
                'user'  => new AuthUserResource($result['user']),
                'token' => $result['token'],
            ], 'Registration successful', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Registration failed', 500);
        }
    }
}
