<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetProfileAction;
use App\Action\Api\UpdateProfileImageAction;
use App\Action\Api\UpdateProfileInfoAction;
use App\Action\Api\DeleteProfileImageAction;
use App\Action\Api\ListSessionsAction;
use App\Action\Api\RevokeSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileImageRequest;
use App\Http\Requests\Api\UpdateProfileInfoRequest;
use App\Http\Resources\Api\MealResource;
use App\Http\Resources\Api\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request, GetProfileAction $action): JsonResponse
    {
        $payload = $action->execute($request->user());

        return $this->success($payload, 'Profile retrieved successfully');
    }

    public function updateImage(UpdateProfileImageRequest $request, UpdateProfileImageAction $action): JsonResponse
    {
        $user = $request->user();

        $result = $action->execute($user, $request->file('image'));

        return $this->success($result, 'Profile image updated successfully');
    }

    public function updateInfo(UpdateProfileInfoRequest $request, UpdateProfileInfoAction $action): JsonResponse
    {
        $user = $request->user();

        $result = $action->execute($user, $request->only([
            'username', 'firstname', 'lastname', 'gender', 'birthday', 'email', 'phone', 'country_code', 'preferred_languages'
        ]));

        if (! $result['updated']) {
            return $this->error('No data provided to update', 400);
        }

        return $this->success($result['data'], 'Profile updated successfully');
    }

    public function deleteImage(Request $request, DeleteProfileImageAction $action): JsonResponse
    {
        $user = $request->user();

        if (! $user->profile_image) {
            return $this->error('No profile image to delete', 404);
        }

        $action->execute($user);

        return $this->success(null, 'Profile image deleted successfully');
    }

    public function sessions(Request $request, ListSessionsAction $action): JsonResponse
    {
        $sessions = $action->execute($request->user());

        return $this->success($sessions, 'Sessions retrieved successfully');
    }

    public function destroySession(Request $request, string $tokenId, RevokeSessionAction $action): JsonResponse
    {
        $user = $request->user();

        $ok = $action->execute($user, $tokenId);

        if (! $ok) {
            return $this->error('Cannot revoke this session', 400);
        }

        return $this->success(null, 'Session revoked successfully');
    }
}
