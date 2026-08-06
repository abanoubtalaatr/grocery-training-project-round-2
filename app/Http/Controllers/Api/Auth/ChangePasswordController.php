<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\ChangePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ChangePasswordController extends Controller
{
    use ApiTrait;

    public function __invoke(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        try {
            $action->run($request->user(), $request->input('password'));

            return $this->successResponse('Password changed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to change password', 500);
        }
    }
}
