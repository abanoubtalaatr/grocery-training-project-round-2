<?php

namespace App\Actions\Admin\Auth;

use Illuminate\Support\Facades\Auth;

class LogoutAdminAction
{
    public function run(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
