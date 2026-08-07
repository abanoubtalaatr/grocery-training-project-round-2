<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Actions\Admin\UserUpdateAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserStatusController extends Controller
{
    public function __construct(protected UserUpdateAction $userUpdateAction)
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userUpdateAction->execute($user, ['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.users.show', $user)->with('success', "User has been {$status}.");
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userUpdateAction->execute($user, ['is_admin' => ! $user->is_admin]);

        $status = $user->is_admin ? 'promoted' : 'demoted';

        return redirect()->route('admin.users.show', $user)->with('success', "User has been {$status}.");
    }
}
