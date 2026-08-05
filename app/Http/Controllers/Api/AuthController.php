<?php

namespace App\Http\Controllers\Api;

use App\Action\Auth\RegisterUserAction;
use App\Action\Auth\LoginUserAction;
use App\Action\Auth\LogoutUserAction;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterApiRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    public function register(RegisterApiRequest $request, RegisterUserAction $action): JsonResponse
    {
        $data = $action->handle($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $data['user']->id,
                    'username' => $data['user']->username,
                    'email' => $data['user']->email,
                    'phone' => $data['user']->phone,
                    'created_at' => $data['user']->created_at,
                ],
                'token' => $data['token'],
            ],
        ], 201);
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $data = $action->handle($request->input('login'), $request->input('password'));

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $data['user']->id,
                    'username' => $data['user']->username,
                    'email' => $data['user']->email,
                    'phone' => $data['user']->phone,
                ],
                'token' => $data['token'],
            ],
        ]);
    }

    public function logout(LogoutUserAction $action): JsonResponse
    {
        $action->handle(request()->user());

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('identifier'));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully. Please check your email or phone.',
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $isValid = $this->authService->verifyOtp($request->input('identifier'), $request->input('otp'));

        if (! $isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->input('identifier'), $request->input('otp'), $request->input('password'));

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    public function me(): JsonResponse
    {
        $user = request()->user();

        return response()->json([
            'success' => true,
            'data' => ['user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified' => $user->email_verified,
                'phone_verified' => $user->phone_verified,
                'created_at' => $user->created_at,
            ]],
        ]);
    }

    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $this->authService->deleteAccount($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->input('password')]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }
}
