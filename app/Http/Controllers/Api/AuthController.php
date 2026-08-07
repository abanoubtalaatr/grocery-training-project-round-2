<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\ChangePasswordAction;
use App\Actions\Auth\DeleteAccountAction;
use App\Actions\Auth\ForgotPasswordAction;
use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\MeAction;
use App\Actions\Auth\RegisterAction;
use App\Actions\Auth\ResetPasswordAction;
use App\Actions\Auth\VerifyOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected RegisterAction $registerAction,
        protected LoginAction $loginAction,
        protected LogoutAction $logoutAction,
        protected ForgotPasswordAction $forgotPasswordAction,
        protected VerifyOtpAction $verifyOtpAction,
        protected ResetPasswordAction $resetPasswordAction,
        protected DeleteAccountAction $deleteAccountAction,
        protected ChangePasswordAction $changePasswordAction,
        protected MeAction $meAction,
    ) {}

    /**
     * Register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->registerAction->execute($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'username' => $result['user']->username,
                        'email' => $result['user']->email,
                        'phone' => $result['user']->phone,
                        'created_at' => $result['user']->created_at,
                    ],
                    'token' => $result['token'],
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->loginAction->execute(
                $request->input('login'),
                $request->input('password')
            );

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'username' => $result['user']->username,
                        'email' => $result['user']->email,
                        'phone' => $result['user']->phone,
                    ],
                    'token' => $result['token'],
                ],
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'errors' => $e->errors(),
            ], 401);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {

            $this->logoutAction->execute($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Forgot Password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {

            $this->forgotPasswordAction->execute(
                $request->input('identifier')
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully. Please check your email or phone.',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {

            $isValid = $this->verifyOtpAction->execute(
                $request->identifier,
                $request->otp
            );

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

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset Password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {

            $this->resetPasswordAction->execute(
                $request->identifier,
                $request->otp,
                $request->password
            );

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Password reset failed',
                'errors' => $e->errors(),
            ], 400);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Password reset failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Current User
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->meAction->execute($request->user()),
        ]);
    }

    /**
     * Delete Account
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        try {

            $this->deleteAccountAction->execute($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change Password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {

            $this->changePasswordAction->execute(
                $request->user(),
                $request->password
            );

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}