<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\ChangePasswordAction;
use App\Action\Api\DeleteAccountAction;
use App\Action\Api\ForgotPasswordAction;
use App\Action\Api\LoginAction;
use App\Action\Api\LogoutAction;
use App\Action\Api\RegisterAction;
use App\Action\Api\ResetPasswordAction;
use App\Action\Api\VerifyOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\DeleteAccountRequest;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\Api\AuthResource;
use App\Http\Resources\Api\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request, RegisterAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->success(new AuthResource($result), 'Registration successful', 201);
    }

    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->success(new AuthResource($result), 'Login successful');
    }

    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute($request->user());

        return $this->success(null, 'Logout successful');
    }

    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $action->execute($request->validated());

        return $this->success(null, 'OTP sent successfully');
    }

    public function verifyOtp(VerifyOtpRequest $request, VerifyOtpAction $action): JsonResponse
    {
        $action->execute($request->validated());

        return $this->success(null, 'OTP verified successfully');
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $action->execute($request->validated());

        return $this->success(null, 'Password reset successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()), 'User retrieved successfully');
    }

    public function deleteAccount(DeleteAccountRequest $request, DeleteAccountAction $action): JsonResponse
    {
        $action->execute($request->user());

        return $this->success(null, 'Account deleted successfully');
    }

    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->validated());

        return $this->success(null, 'Password changed successfully');
    }
}
