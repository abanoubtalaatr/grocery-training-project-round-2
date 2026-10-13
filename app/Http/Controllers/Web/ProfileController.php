<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $user->load(['addresses', 'orders' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
        }, 'orders.items']);

        $activeOrdersCount = $user->orders()->active()->count();
        $totalOrdersCount = $user->orders()->count();
        $addressesCount = $user->addresses()->count();

        return view('profile.show', [
            'user' => $user,
            'activeOrdersCount' => $activeOrdersCount,
            'totalOrdersCount' => $totalOrdersCount,
            'addressesCount' => $addressesCount,
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function updateInfo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,' . $request->user()->id],
            'email' => ['nullable', 'email', 'unique:users,email,' . $request->user()->id],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,' . $request->user()->id],
            'country_code' => ['nullable', 'string', 'max:10'],
            'gender' => ['nullable', 'in:male,female,other'],
            'birthday' => ['nullable', 'date'],
        ]);

        $request->user()->update($validated);

        return back()->with('success', trans('orders.profile.update_success'));
    }

    public function image(Request $request): View
    {
        return view('profile.image', [
            'user' => $request->user(),
        ]);
    }

    public function uploadImage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profile_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $request->file('profile_image')->store('profile-images', 'public');
        $user->update(['profile_image' => $path]);

        return back()->with('success', trans('orders.profile.image_upload_success'));
    }

    public function deleteImage(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->update(['profile_image' => null]);
        }

        return back()->with('success', trans('orders.profile.image_delete_success'));
    }
}
