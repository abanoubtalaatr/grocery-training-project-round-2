<?php

namespace App\Action\Admin\Setting;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdateSettingsAction
{
    public function execute(array $data, Request $request)
    {
        $settings = Setting::getSettings();

        // Handle file uploads
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            // Delete old favicon
            if ($settings->favicon && Storage::disk('public')->exists($settings->favicon)) {
                Storage::disk('public')->delete($settings->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        $settings->update($data);

        return $settings->fresh();
    }
}
