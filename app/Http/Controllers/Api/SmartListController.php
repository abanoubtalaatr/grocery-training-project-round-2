<?php

namespace App\Http\Controllers\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Traits\ApiResponseTrait;
use App\Traits\HandlesImageUploads;
use App\Traits\ManagesPivotRelation;

class SmartListController extends Controller
{
    use ApiResponseTrait, HandlesImageUploads, ManagesPivotRelation;

    public function index(Request $request)
    {
        $smartLists = SmartList::where('user_id', $request->user()->id)->with('meals')->get();

        return $this->successResponse(
            'Smart lists retrieved successfully',
            SmartListResource::collection($smartLists)
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
            $data['image'] = $this->storeUploadedImage($request->file('image'), 'smart-lists');
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        $smartList->load('meals');

        return $this->successResponse('Wish list created successfully', new SmartListResource($smartList));
    }

    public function show(Request $request, $id)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->with('meals')->findOrFail($id);

        return $this->successResponse('Smart list retrieved successfully', new SmartListResource($smartList));
    }

public function update(SmartListRequest $request, $id)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $data = $request->validated();

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUploadedImage($request->file('image'), 'smart-lists');
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        $smartList->load('meals');

        return $this->successResponse('Wish list updated successfully', new SmartListResource($smartList));
    }

    public function destroy(Request $request, $id)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $smartList->meals()->detach();
        $smartList->delete();

        return $this->successResponse('Wish list deleted successfully');
    }

    /**
     * Add a meal to a wish list.
     */
    public function addMeal(Request $request, string $id)
    {
        $request->validate(['meal_id' => ['required', 'exists:meals,id']]);

        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $this->attachRelated($smartList, 'meals', $request->meal_id);

        return $this->successResponse('Item added to wish list successfully', new SmartListResource($smartList));
    }

    /**
     * Remove a meal from a wish list.
     */
    public function removeMeal(Request $request, string $id, string $mealId)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $this->detachRelated($smartList, 'meals', $mealId);

        return $this->successResponse('Item removed from wish list successfully', new SmartListResource($smartList));
    }
}
