<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Setting\UpdateSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::getSettings();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(UpdateSettingsRequest $request, UpdateSettingsAction $action): RedirectResponse
    {
        $action->run($request->validated());

        return back()->with('success', 'General settings updated successfully.');
    }
}
