<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function index(Request $request): View
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        $currentSessionId = $request->session()->getId();

        return view('profile.sessions', [
            'sessions' => $sessions,
            'currentSessionId' => $currentSessionId,
        ]);
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        if ($id === $request->session()->getId()) {
            return back()->withErrors(['session' => 'You cannot revoke your current session.']);
        }

        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('success', trans('auth.sessions.success'));
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        $password = $request->input('password');

        if (! $password || ! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $password,
        ])) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return back()->with('success', trans('auth.sessions.success_others'));
    }
}
