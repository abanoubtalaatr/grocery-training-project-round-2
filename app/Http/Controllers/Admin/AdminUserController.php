<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\User\CreateUserAction;
use App\Action\Admin\User\DeleteUserAction;
use App\Action\Admin\User\GetUsersAction;
use App\Action\Admin\User\ToggleUserStatusAction;
use App\Action\Admin\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request, GetUsersAction $action)
    {
        $result = $action->execute($request->all());
        $users = $result['users'];

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request, CreateUserAction $action)
    {
        $action->execute($request->validated());

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $action)
    {
        $action->execute($user, $request->validated());

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user, DeleteUserAction $action)
    {
        $action->execute($user);

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    public function toggleStatus(User $user, ToggleUserStatusAction $action)
    {
        $action->execute($user);

        return back()->with('success', 'User status updated');
    }
}
