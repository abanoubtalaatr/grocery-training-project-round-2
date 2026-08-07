<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Actions\Admin\UserIndexAction;
use App\Actions\Admin\UserShowAction;
use App\Actions\Admin\UserStoreAction;
use App\Actions\Admin\UserUpdateAction;
use App\Actions\Admin\UserDeleteAction;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function __construct(
        protected UserIndexAction $userIndexAction,
        protected UserShowAction $userShowAction,
        protected UserStoreAction $userStoreAction,
        protected UserUpdateAction $userUpdateAction,
        protected UserDeleteAction $userDeleteAction
    ) {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $data = $this->userIndexAction->execute($request);

        return view('admin.users.index', $data);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user = $this->userShowAction->execute($user);
        $orderCount = $user->orders()->count();
        $totalSpent = $user->orders()->sum('total');

        return view('admin.users.show', compact('user', 'orderCount', 'totalSpent'));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->userStoreAction->execute($request->validated());

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Handle email verification timestamp
        if (array_key_exists('email_verified', $validated)) {
            $validated['email_verified_at'] = $validated['email_verified'] ? now() : null;
        }

        // Handle phone verification timestamp
        if (array_key_exists('phone_verified', $validated)) {
            $validated['phone_verified_at'] = $validated['phone_verified'] ? now() : null;
        }

        $this->userUpdateAction->execute($user, $validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->userDeleteAction->execute($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
