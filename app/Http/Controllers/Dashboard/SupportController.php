<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Support\DestroySupportAction;
use App\Actions\Admin\Support\IndexSupportAction;
use App\Actions\Admin\Support\ShowSupportAction;
use App\Actions\Admin\Support\UpdateSupportStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Support\UpdateSupportStatusRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request, IndexSupportAction $action): View
    {
        $messages = $action->run($request);

        return view('admin.support.index', compact('messages'));
    }

    public function show(ContactMessage $support, ShowSupportAction $action): View
    {
        $support = $action->run($support);

        return view('admin.support.show', compact('support'));
    }

    public function updateStatus(UpdateSupportStatusRequest $request, ContactMessage $support, UpdateSupportStatusAction $action): RedirectResponse
    {
        $action->run($support, $request->validated());

        return back()->with('success', 'Support message updated.');
    }

    public function destroy(ContactMessage $support, DestroySupportAction $action): RedirectResponse
    {
        $action->run($support);

        return redirect()->route('admin.support.index')->with('success', 'Message deleted.');
    }
}
