<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\VerifyResetOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyOtpRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class VerifyOtpController extends Controller
{
    use ApiTrait;

    public function __invoke(VerifyOtpRequest $request, VerifyResetOtpAction $action): JsonResponse
    {
        try {
            $isValid = $action->run(
                $request->input('identifier'),
                $request->input('otp')
            );

            if (! $isValid) {
                return $this->errorResponse([], 'Invalid or expired OTP', 400);
            }

            return $this->successResponse('OTP verified successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'OTP verification failed', 500);
        }
    }
}
