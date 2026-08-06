<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Profile\DeleteProfileImageAction;
use App\Actions\Api\Profile\UpdateProfileImageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileImageRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileImageController extends Controller
{
    use ApiTrait;

    public function update(UpdateProfileImageRequest $request, UpdateProfileImageAction $action): JsonResponse
    {
        try {
            $user = $action->run($request->user(), $request->file('image'));

            return $this->dataResponse([
                'profile_image' => $user->profile_image,
                'profile_image_url' => $user->profile_image_url,
            ], 'Profile image updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to update profile image', 500);
        }
    }

    public function destroy(Request $request, DeleteProfileImageAction $action): JsonResponse
    {
        try {
            $deleted = $action->run($request->user());

            if (!$deleted) {
                return $this->errorResponse([], 'No profile image to delete', 404);
            }

            return $this->successResponse('Profile image deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to delete profile image', 500);
        }
    }
}
