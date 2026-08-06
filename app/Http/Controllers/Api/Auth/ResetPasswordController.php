<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\ResetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    use ApiTrait;

    public function __invoke(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        try {
            $action->run(
                $request->input('identifier'),
                $request->input('otp'),
                $request->input('password')
            );

            return $this->successResponse('Password reset successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->errors(), 'Password reset failed', 400);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Password reset failed', 500);
        }
    }
}
