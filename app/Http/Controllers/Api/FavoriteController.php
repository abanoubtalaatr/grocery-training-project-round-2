<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Favorite\AddFavoriteAction;
use App\Action\Favorite\RemoveFavoriteAction;
use App\Action\Favorite\ListFavoritesAction;
use App\Http\Requests\Api\FavoriteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request, ListFavoritesAction $action): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->input('per_page', 15);
        $paginator = $action->handle($user, $perPage);

        $items = $paginator->getCollection()->map(function ($meal) use ($user) {
            return [
                'id' => $meal->id,
                'title' => $meal->title,
                'slug' => $meal->slug,
                'image_url' => $meal->image_url,
                'price' => $meal->price ?? null,
                'discount_price' => $meal->discount_price ?? null,
                'is_favorited' => true,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Favorites retrieved successfully',
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(FavoriteRequest $request, AddFavoriteAction $action): JsonResponse
    {
        $user = $request->user();
        try {
            $res = $action->handle($user, (int) $request->input('meal_id'));
            return response()->json($res);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function destroy(Request $request, RemoveFavoriteAction $action, $mealId): JsonResponse
    {
        $user = $request->user();
        $res = $action->handle($user, (int) $mealId);
        return response()->json($res);
    }
}
