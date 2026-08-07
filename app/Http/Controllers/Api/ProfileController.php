<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\DeleteProfileImageAction;
use App\Action\Api\GetProfileAction;
use App\Action\Api\GetUserSessionsAction;
use App\Action\Api\RevokeSessionAction;
use App\Action\Api\UpdateProfileImageAction;
use App\Action\Api\UpdateProfileInfoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileImageRequest;
use App\Http\Requests\Api\UpdateProfileInfoRequest;
use App\Http\Resources\Api\ProfileResource;
use App\Http\Resources\Api\SessionResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request, GetProfileAction $action): JsonResponse
    {
        $user = $request->user();
        $profileData = $action->execute($user);

        return $this->success(new ProfileResource($profileData),'Profile retrieved successfully');
    }

    public function updateImage(UpdateProfileImageRequest $request, UpdateProfileImageAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($user, $request->file('image'));

        return $this->success($result,'Profile image updated successfully');
    }

    public function updateInfo(UpdateProfileInfoRequest $request, UpdateProfileInfoAction $action): JsonResponse
    {
        $user = $request->user();
        $updatedUser = $action->execute($user, $request->validated());

        return $this->success(
            [
                'id' => $updatedUser->id,
                'username' => $updatedUser->username,
                'firstname' => $updatedUser->firstname,
                'lastname' => $updatedUser->lastname,
                'full_name' => $updatedUser->full_name,
                'gender' => $updatedUser->gender,
                'birthday' => $updatedUser->birthday?->format('Y-m-d'),
                'email' => $updatedUser->email,
                'phone' => $updatedUser->phone,
                'country_code' => $updatedUser->country_code,
                'preferred_languages' => $updatedUser->preferred_languages ?? [],
                'profile_image_url' => $updatedUser->profile_image_url,
                'updated_at' => $updatedUser->updated_at,
            ],
            'Profile updated successfully'
        );
    }

    public function deleteImage(Request $request, DeleteProfileImageAction $action): JsonResponse
    {
        $user = $request->user();
        $action->execute($user);

        return $this->success(null, 'Profile image deleted successfully');
    }

    public function sessions(Request $request, GetUserSessionsAction $action): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->id;
        $tokens = $action->execute($user, $currentTokenId);

        return $this->success(SessionResource::collection($tokens),'Sessions retrieved successfully');
    }

    public function destroySession(Request $request, string $tokenId, RevokeSessionAction $action): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->id;

        $action->execute($user, $tokenId, $currentTokenId);

        return $this->success(null, 'Session revoked successfully');
    }
}
