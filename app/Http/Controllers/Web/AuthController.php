<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\WebDeleteAccountRequest;
use App\Http\Requests\Web\WebForgotPasswordRequest;
use App\Http\Requests\Web\WebLoginRequest;
use App\Http\Requests\Web\WebMfaVerifyRequest;
use App\Http\Requests\Web\WebRegisterRequest;
use App\Http\Requests\Web\WebResetPasswordRequest;
use App\Http\Requests\Web\WebVerifyEmailOtpRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected OtpService $otpService
    ) {}

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(WebRegisterRequest $request): RedirectResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            Auth::login($result['user'], true);
            $request->session()->regenerate();

            if ($result['user']->email) {
                return redirect()->route('verify-email.form')->with('success', trans('auth.register.success'));
            }

            return redirect()->route('login')->with('success', trans('auth.register.success'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Registration failed', ['message' => $e->getMessage()]);

            return back()->withErrors(['username' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(WebLoginRequest $request): RedirectResponse
    {
        try {
            $result = $this->authService->login(
                $request->input('login'),
                $request->input('password')
            );

            Auth::login($result['user'], true);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))->with('success', trans('auth.login.success'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function showMfaForm(): View
    {
        $identifier = session('auth.mfa.identifier');

        if (! $identifier) {
            return redirect()->route('login');
        }

        return view('auth.mfa-verify', ['identifier' => $identifier]);
    }

    public function verifyMfa(WebMfaVerifyRequest $request): RedirectResponse
    {
        $identifier = session('auth.mfa.identifier');

        if (! $identifier) {
            return redirect()->route('login');
        }

        try {
            $otp = $request->input('otp');

            if (! $this->otpService->verify($identifier, $otp, 'mfa_login')) {
                return back()->withErrors(['otp' => trans('auth.validation.otp.required')])->withInput();
            }

            $user = User::findByIdentifier($identifier);

            if (! $user || ! $user->is_active) {
                return redirect()->route('login')->withErrors(['login' => 'Account not found or deactivated.']);
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            $request->session()->forget('auth.mfa');

            return redirect()->intended(route('home'))->with('success', trans('auth.mfa.success'));
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()])->withInput();
        }
    }

    public function resendMfa(Request $request): RedirectResponse
    {
        $identifier = session('auth.mfa.identifier');

        if (! $identifier) {
            return redirect()->route('login');
        }

        try {
            $otp = $this->otpService->generate($identifier, 'mfa_login');

            $notificationService = app(\App\Services\NotificationService::class);

            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $notificationService->sendOtpEmail($identifier, $otp, 'mfa_login');
            } else {
                $notificationService->sendOtpSms($identifier, $otp, 'mfa_login');
            }

            return back()->with('success', 'Verification code resent.');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => 'Failed to resend code.']);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(WebForgotPasswordRequest $request): RedirectResponse
    {
        try {
            $this->authService->forgotPassword($request->input('identifier'));

            $request->session()->put('password_reset.identifier', $request->input('identifier'));

            return redirect()->route('password.reset.form')->with('success', trans('auth.forgot_password.success'));
        } catch (\Exception $e) {
            return back()->withErrors(['identifier' => $e->getMessage()])->withInput();
        }
    }

    public function showResetPasswordForm(): View
    {
        $identifier = session('password_reset.identifier');

        return view('auth.reset-password', ['identifier' => $identifier]);
    }

    public function resetPassword(WebResetPasswordRequest $request): RedirectResponse
    {
        try {
            $this->authService->resetPassword(
                $request->input('identifier'),
                $request->input('otp'),
                $request->input('password')
            );

            $request->session()->forget('password_reset');

            return redirect()->route('login')->with('success', trans('auth.reset_password.success'));
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()])->withInput();
        }
    }

    public function showVerifyEmail(): View
    {
        return view('auth.verify-email');
    }

    public function verifyEmailOtp(WebVerifyEmailOtpRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return redirect()->route('login');
            }

            $otp = $request->input('otp');

            if (! $this->otpService->verify($user->email, $otp, 'email_verification')) {
                return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
            }

            $user->email_verified = true;
            $user->email_verified_at = now();
            $user->save();

            return redirect()->route('home')->with('success', trans('auth.verify_email.success'));
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()])->withInput();
        }
    }

    public function resendEmailVerification(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! $user || ! $user->email) {
                return back()->withErrors(['otp' => 'No email address found.']);
            }

            $otp = $this->otpService->generate($user->email, 'email_verification');

            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendOtpEmail($user->email, $otp, 'email_verification');

            return back()->with('success', 'Verification code resent.');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => 'Failed to resend code.']);
        }
    }

    public function confirmDeleteAccount(): View
    {
        return view('profile.delete-account');
    }

    public function deleteAccount(WebDeleteAccountRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! Hash::check($request->input('password'), $user->password)) {
                return back()->withErrors(['password' => 'Password is incorrect.'])->withInput();
            }

            $this->authService->deleteAccount($user);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('success', trans('auth.delete_account.success'));
        } catch (\Exception $e) {
            return back()->withErrors(['password' => $e->getMessage()])->withInput();
        }
    }
}
