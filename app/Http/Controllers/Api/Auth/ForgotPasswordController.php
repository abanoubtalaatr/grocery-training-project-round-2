<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\SendPasswordResetOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    use ApiTrait;

    public function __invoke(ForgotPasswordRequest $request, SendPasswordResetOtpAction $action): JsonResponse
    {
        try {
            $action->run($request->input('identifier'));

            return $this->successResponse('OTP sent successfully. Please check your email or phone.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to send OTP', 500);
        }
    }
}
