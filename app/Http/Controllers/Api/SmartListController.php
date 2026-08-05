<?php

namespace App\Http\Controllers\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;

class SmartListController extends Controller
{
    public function index(Request $request)
    {
        $smartLists = $this->userSmartListsQuery($request)->with('meals')->get();

        return $this->successResponse(
            SmartListResource::collection($smartLists),
            'Smart lists retrieved successfully'
        );
    }

    public function store(SmartListRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['description'] = $data['description'] ?? '';
        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        $smartList->load('meals');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Wish list created successfully'
        );
    }

    public function show(Request $request, $id)
    {
        $smartList = $this->findUserSmartList($request, $id)->load('meals');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Smart list retrieved successfully'
        );
    }

    public function update(SmartListRequest $request, $id)
    {
        $smartList = $this->findUserSmartList($request, $id);
        $data = $request->validated();

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        $smartList->load('meals');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Wish list updated successfully'
        );
    }

    public function destroy(Request $request, $id)
    {
        $smartList = $this->findUserSmartList($request, $id);
        $smartList->meals()->detach();
        $smartList->delete();

        return $this->successResponse(null, 'Wish list deleted successfully');
    }

    /**
     * Add a meal to a wish list.
     */
    public function addMeal(Request $request, string $id)
    {
        $request->validate(['meal_id' => ['required', 'exists:meals,id']]);

        $smartList = $this->findUserSmartList($request, $id);
        $smartList->meals()->syncWithoutDetaching([$request->meal_id]);
        $smartList->load('meals');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Item added to wish list successfully'
        );
    }

    /**
     * Remove a meal from a wish list.
     */
    public function removeMeal(Request $request, string $id, string $mealId)
    {
        $smartList = $this->findUserSmartList($request, $id);
        $smartList->meals()->detach($mealId);
        $smartList->load('meals');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Item removed from wish list successfully'
        );
    }

    /**
     * Base query scoped to the authenticated user's smart lists.
     */
    private function userSmartListsQuery(Request $request)
    {
        return SmartList::where('user_id', $request->user()->id);
    }

    /**
     * Find a smart list by id, scoped to the authenticated user.
     */
    private function findUserSmartList(Request $request, $id): SmartList
    {
        return $this->userSmartListsQuery($request)->findOrFail($id);
    }

    /**
     * Store an uploaded image and return its generated file name.
     */
    private function storeImage($image): string
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/smart-lists'), $imageName);

        return $imageName;
    }

    /**
     * Build a consistent success JSON response.
     */
    private function successResponse($data, string $message)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }
}