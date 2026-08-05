<?php

namespace App\Http\Controllers\Api;

use App\Action\Profile\GetProfileAction;
use App\Action\Profile\UpdateProfileAction;
use App\Action\Profile\UpdateProfileImageAction;
use App\Action\Profile\DeleteProfileImageAction;
use App\Action\Profile\ListSessionsAction;
use App\Action\Profile\DestroySessionAction;
use App\Action\Profile\ProfilePresenter;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\UpdateProfileImageRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show(GetProfileAction $action, ProfilePresenter $presenter): JsonResponse
    {
        $user = request()->user();

        $payload = $action->handle($user);

        $data = $presenter->present($payload);

        return response()->json(['success' => true, 'message' => 'Profile retrieved successfully', 'data' => $data]);
    }

    public function updateImage(UpdateProfileImageRequest $request, UpdateProfileImageAction $action, ProfilePresenter $presenter): JsonResponse
    {
        $user = $request->user();
        $image = $request->file('image');

        $user = $action->handle($user, $image);

        // Reuse presenter to format minimal response
        $data = $presenter->present(['user' => $user, 'addresses' => collect(), 'orders' => collect(), 'notifications' => collect(), 'wishlist' => collect()]);

        return response()->json(['success' => true, 'message' => 'Profile image updated successfully', 'data' => $data['me']]);
    }

    public function updateInfo(UpdateProfileRequest $request, UpdateProfileAction $action, ProfilePresenter $presenter): JsonResponse
    {
        $user = $request->user();

        $updated = $action->handle($user, $request->validated());

        $data = $presenter->present(['user' => $updated, 'addresses' => collect(), 'orders' => collect(), 'notifications' => collect(), 'wishlist' => collect()]);

        return response()->json(['success' => true, 'message' => 'Profile updated successfully', 'data' => $data['me']]);
    }

    public function deleteImage(DeleteProfileImageAction $action, ProfilePresenter $presenter): JsonResponse
    {
        $user = request()->user();

        $user = $action->handle($user);

        $data = $presenter->present(['user' => $user, 'addresses' => collect(), 'orders' => collect(), 'notifications' => collect(), 'wishlist' => collect()]);

        return response()->json(['success' => true, 'message' => 'Profile image deleted successfully', 'data' => $data['me']]);
    }

    public function sessions(ListSessionsAction $action): JsonResponse
    {
        $user = request()->user();

        $tokens = $action->handle($user);

        return response()->json(['success' => true, 'message' => 'Sessions retrieved successfully', 'data' => $tokens]);
    }

    public function destroySession(string $tokenId, DestroySessionAction $action): JsonResponse
    {
        $user = request()->user();

        $action->handle($user, $tokenId);

        return response()->json(['success' => true, 'message' => 'Session revoked successfully']);
    }
}
