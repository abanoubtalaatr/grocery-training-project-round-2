<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Notification\DestroyNotificationAction;
use App\Actions\Admin\Notification\IndexNotificationAction;
use App\Actions\Admin\Notification\SendBroadcastNotificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notification\SendBroadcastNotificationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationBroadcastController extends Controller
{
    public function index(Request $request, IndexNotificationAction $action): View
    {
        $data = $action->run($request);

        return view('admin.notifications.index', $data);
    }

    public function create(): View
    {
        $users = User::where('is_admin', false)->orderBy('created_at', 'desc')->get();

        return view('admin.notifications.create', compact('users'));
    }

    public function store(SendBroadcastNotificationRequest $request, SendBroadcastNotificationAction $action): RedirectResponse
    {
        $sentCount = $action->run($request->validated());

        return redirect()->route('admin.notifications.index')
            ->with('success', "Notification broadcast sent to {$sentCount} user(s).");
    }

    public function destroy(string $id, DestroyNotificationAction $action): RedirectResponse
    {
        $action->run($id);

        return redirect()->route('admin.notifications.index')->with('success', 'Notification deleted.');
    }
}
