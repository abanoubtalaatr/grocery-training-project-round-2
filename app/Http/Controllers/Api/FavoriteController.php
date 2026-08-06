<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Favorite\DestroyFavoriteAction;
use App\Actions\Api\Favorite\GetFavoritesAction;
use App\Actions\Api\Favorite\StoreFavoriteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Traits\ApiTrait;

class FavoriteController extends Controller
{
    use ApiTrait;

    public function index(GetFavoritesAction $action)
    {
        return $this->dataResponse(
            FavoriteResource::collection(
                $action->run(auth()->user())
            ),
            'Favorites retrieved successfully'
        );
    }

    public function store(StoreFavoriteRequest $request, StoreFavoriteAction $action)
    {
        $favorite = $action->run(
            auth()->user(),
            $request->validated()['meal_id']
        );

        return $this->dataResponse(
            new FavoriteResource($favorite),
            'Added to favorites',
            201
        );
    }

    public function destroy(int $mealId, DestroyFavoriteAction $action)
    {
        $action->run(
            auth()->user(),
            $mealId
        );

        return $this->successResponse(
            'Removed from favorites'
        );
    }
}