<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Profile\ShowProfileAction;
use App\Actions\Api\Profile\UpdateProfileInfoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileInfoRequest;
use App\Http\Resources\Api\ProfileResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiTrait;

    /**
     * Get full user profile
     */
    public function show(Request $request, ShowProfileAction $action): JsonResponse
    {
        try {
            $user = $action->run($request->user());

            return $this->dataResponse(
                new ProfileResource($user),
                'Profile retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to retrieve profile', 500);
        }
    }

    /**
     * Update profile information
     */
    public function update(UpdateProfileInfoRequest $request, UpdateProfileInfoAction $action): JsonResponse
    {
        try {
            $data = $request->validated();

            if (empty($data) && !$request->has('preferred_languages')) {
                return $this->errorResponse([], 'No data provided to update', 400);
            }

            $user = $action->run($request->user(), $data);

            return $this->dataResponse([
                'id' => $user->id,
                'username' => $user->username,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'full_name' => $user->full_name,
                'gender' => $user->gender,
                'birthday' => $user->birthday?->format('Y-m-d'),
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
                'preferred_languages' => $user->preferred_languages ?? [],
                'profile_image_url' => $user->profile_image_url,
                'updated_at' => $user->updated_at,
            ], 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to update profile', 500);
        }
    }
}
