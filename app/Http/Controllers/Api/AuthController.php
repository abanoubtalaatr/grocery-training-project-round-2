<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\RegisterUserAction;
use App\Action\Api\LoginUserAction;
use App\Action\Api\LogoutUserAction;
use App\Action\Api\ForgotPasswordAction;
use App\Action\Api\VerifyOtpAction;
use App\Action\Api\ResetPasswordAction;
use App\Action\Api\DeleteAccountAction;
use App\Action\Api\ChangePasswordAction;
use App\Action\Api\GetMeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\Api\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registration successful', 201);
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action->execute($request->input('login'), $request->input('password'));

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    public function logout(Request $request, LogoutUserAction $action): JsonResponse
    {
        $action->execute($request->user());

        return $this->success(null, 'Logout successful');
    }

    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $action->execute($request->input('identifier'));

        return $this->success(null, 'OTP sent successfully. Please check your email or phone.');
    }

    public function verifyOtp(VerifyOtpRequest $request, VerifyOtpAction $action): JsonResponse
    {
        $isValid = $action->execute($request->input('identifier'), $request->input('otp'));

        if (! $isValid) {
            return $this->error('Invalid or expired OTP', 400);
        }

        return $this->success(null, 'OTP verified successfully');
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $action->execute($request->input('identifier'), $request->input('otp'), $request->input('password'));

        return $this->success(null, 'Password reset successfully');
    }

    public function me(Request $request, GetMeAction $action): JsonResponse
    {
        $user = $action->execute($request->user());

        return $this->success(['user' => new UserResource($user)]);
    }

    public function deleteAccount(DeleteAccountRequest $request, DeleteAccountAction $action): JsonResponse
    {
        $action->execute($request->user());

        return $this->success(null, 'Account deleted successfully');
    }

    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->input('password'));

        return $this->success(null, 'Password changed successfully');
    }
}
