<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\GoogleAuthLoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GoogleAuthLoginRequest;
use App\Http\Resources\Api\AuthUserResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class GoogleAuthController extends Controller
{
    use ApiTrait;

    public function __invoke(GoogleAuthLoginRequest $request, GoogleAuthLoginAction $action): JsonResponse
    {
        try {
            $result = $action->run(
                $request->validated('id_token'),
                $request->validated('device_name')
            );

            return $this->dataResponse([
                'user' => new AuthUserResource($result['user']),
                'token' => $result['token'],
            ], 'Login successful');
        } catch (RuntimeException $e) {
            return $this->errorResponse([], $e->getMessage(), $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 'Google sign-in failed. Please try again.', 500);
        }
    }
}
