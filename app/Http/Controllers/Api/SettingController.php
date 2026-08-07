<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SettingResource;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponse;

    /**
     * Get all settings
     */
    public function index(): JsonResponse
    {
        $settings = Setting::getSettings();

        return $this->success(
            new SettingResource($settings),
            'Settings retrieved successfully'
        );
    }

    /**
     * Get single setting (not typically used for singleton settings)
     */
    public function show(Setting $setting): JsonResponse
    {
        return $this->success(
            new SettingResource($setting),
            'Setting retrieved successfully'
        );
    }

    /**
     * Create new setting (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Setting::class);

        $validated = $request->validate([
            'key' => ['required', 'string', 'unique:settings,key'],
            'value' => ['required', 'string'],
            'group' => ['nullable', 'string'],
        ]);

        $setting = Setting::create($validated);

        return $this->success(
            new SettingResource($setting),
            'Setting created successfully',
            201
        );
    }

    /**
     * Update settings
     */
    public function update(Request $request, Setting $setting = null): JsonResponse
    {
        $this->authorize('update', Setting::class);

        // For singleton settings, update the global settings
        if (! $setting) {
            $setting = Setting::getSettings();
        }

        $validated = $request->validate([
            'site_name' => ['sometimes', 'string', 'max:255'],
            'site_description' => ['sometimes', 'string', 'max:1000'],
            'facebook' => ['sometimes', 'nullable', 'url'],
            'linkedin' => ['sometimes', 'nullable', 'url'],
            'instagram' => ['sometimes', 'nullable', 'url'],
            'twitter' => ['sometimes', 'nullable', 'url'],
            'email' => ['sometimes', 'email'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'address' => ['sometimes', 'string', 'max:500'],
            'copyright_text' => ['sometimes', 'string', 'max:255'],
            'logo' => ['sometimes', 'nullable', 'file', 'image', 'max:5120'],
            'favicon' => ['sometimes', 'nullable', 'file', 'image', 'max:1024'],
        ]);

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            $validated['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        $setting->update($validated);

        return $this->success(
            new SettingResource($setting),
            'Settings updated successfully'
        );
    }

    /**
     * Delete setting (admin only)
     */
    public function destroy(Setting $setting): JsonResponse
    {
        $this->authorize('delete', Setting::class);

        $setting->delete();

        return $this->success(null, 'Setting deleted successfully');
    }
}
