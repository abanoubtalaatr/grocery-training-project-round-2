<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Auth\LoginAdminAction;
use App\Actions\Admin\Auth\LogoutAdminAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected $loginAction;
    protected $logoutAction;

    public function __construct(LoginAdminAction $loginAction, LogoutAdminAction $logoutAction)
    {
        $this->loginAction = $loginAction;
        $this->logoutAction = $logoutAction;
    }

    /**
     * Show the admin login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login request.
     */
    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $this->loginAction->run($request->only('login', 'password', 'remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Welcome back, ' . auth()->user()->name . '!');
    }

    /**
     * Log the admin out.
     */
    public function logout(): RedirectResponse
    {
        $this->logoutAction->run();

        return redirect()->route('admin.login')
            ->with('success', 'Logged out successfully.');
    }
}
