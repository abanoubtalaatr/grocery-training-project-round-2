<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\LoginUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\Api\AuthUserResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use ApiTrait;

    public function __invoke(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        try {
            $result = $action->run(
                $request->input('login'),
                $request->input('password')
            );

            return $this->dataResponse([
                'user'  => new AuthUserResource($result['user']),
                'token' => $result['token'],
            ], 'Login successful');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->errors(), 'Login failed', 401);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Login failed', 500);
        }
    }
}
