<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\User\IndexUserAction;
use App\Actions\Admin\User\ShowUserAction;
use App\Actions\Admin\User\ToggleActiveUserAction;
use App\Actions\Admin\User\ToggleAdminUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request, IndexUserAction $action): View
    {
        $this->authorize('viewAny', User::class);
        $users = $action->run($request);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user, ShowUserAction $action): View
    {
        $this->authorize('view', $user);
        $user = $action->run($user);

        return view('admin.users.show', compact('user'));
    }

    public function toggleAdmin(User $user, ToggleAdminUserAction $action): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $isAdmin = $action->run($user);

        return back()->with('success', $isAdmin ? 'User promoted to Admin.' : 'Admin privileges revoked.');
    }

    public function toggleActive(User $user, ToggleActiveUserAction $action): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $isActive = $action->run($user);

        return back()->with('success', $isActive ? 'User account activated.' : 'User account deactivated.');
    }
}
