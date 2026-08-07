<?php

namespace App\Action\Api;

use Illuminate\Http\Request;

class UpdateSettingsAction
{
    public function execute($settings, array $data, Request $request)
    {
        // Handle file uploads
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        $settings->update($data);

        return $settings->fresh();
    }
}