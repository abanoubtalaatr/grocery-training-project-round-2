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
            $data = $request->validated();

            if (empty($data) && !$request->has('preferred_languages')) {
                return $this->errorResponse([], 'No data provided to update', 400);
            }

            $user = $action->run($request->user(), $data);

            return $this->dataResponse(new ProfileResource($user), 'Profile updated successfully');
    }
}
