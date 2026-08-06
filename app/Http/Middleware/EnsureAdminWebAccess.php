<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminWebAccess
{
    /**
     * Handle an incoming request.
     * Redirects unauthenticated users to the admin login page.
     * Blocks authenticated non-admin users with 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'يرجى تسجيل الدخول للوصول إلى لوحة التحكم.');
        }

        if (! Auth::user()->is_admin) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->with('error', 'ليس لديك صلاحية الوصول إلى لوحة التحكم.');
        }

        return $next($request);
    }
}
