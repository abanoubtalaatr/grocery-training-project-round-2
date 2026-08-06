<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Favorite\CheckFavoriteAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteStatusResource;
use App\Models\Meal;
use App\Traits\ApiTrait;

class FavoriteStatusController extends Controller
{
    use ApiTrait;

    public function __invoke(Meal $meal, CheckFavoriteAction $action)
    {
        return $this->dataResponse(
            new FavoriteStatusResource(
                $action->run(auth()->user(), $meal)
            )
        );
    }
}