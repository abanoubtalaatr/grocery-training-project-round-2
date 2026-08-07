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
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\Api\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request , RegisterAction $action): JsonResponse
    {
            $result = $action->execute($request->validated());
            return $this->successResponse( 'Registration successfull',new UserResource($result['user']),201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request , LoginAction $action): JsonResponse
    {
            $result = $action->execute(
                $request->input('login'),
                $request->input('password')
            );
            return $this->successResponse('Login successful', [
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request , LogoutAction $action): JsonResponse
    {
            $action->execute($request->user());
            return $this->successResponse('Logout successful');
    }

    /**
     * Forgot password - send OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request , ForgotPasswordAction $action): JsonResponse
    {
            $action->execute($request->input('identifier'));
            return $this->successResponse('OTP sent successfully. Please check your email or phone.');
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request, VerifyOtpAction $action): JsonResponse
    {
        $isValid = $action->execute(
            $request->input('identifier'),
            $request->input('otp')
        );

        if (! $isValid) {
            return $this->errorResponse('Invalid or expired OTP', 400);
        }

        return $this->successResponse('OTP verified successfully');
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $action->execute(
            $request->input('identifier'),
            $request->input('otp'),
            $request->input('password')
        );

        return $this->successResponse('Password reset successfully', null);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse('User retrieved successfully',new UserResource($request->user()));
    }

    /**
     * Delete authenticated user's account
     */
    public function deleteAccount(DeleteAccountRequest $request, DeleteAccountAction $action): JsonResponse
    {
        $action->execute($request->user());

        return $this->successResponse('Account deleted successfully');
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->input('password'));

        return $this->successResponse('Password changed successfully');
    }
}
