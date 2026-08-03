<?php

namespace App\Http\Controllers\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddMealRequest;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Services\SmartListImageService;

class SmartListController extends Controller
{
    public function __construct(protected SmartListImageService $imageService)
    {
    }

    public function index(Request $request)
    {
        $smartLists = SmartList::where('user_id', $request->user()->id)->with('meals')->get();
        return $this->successResponse('Smart lists retrieved successfully', SmartListResource::collection($smartLists));
    }

    public function store(SmartListRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['description'] = $data['description'] ?? '';
        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->upload($request->file('image'));
        }

        $smartList = SmartList::create($data);
        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }
        $smartList->load('meals');

    return $this->successResponse('Wish list created successfully', new SmartListResource($smartList), 201);    }

    public function show(Request $request, SmartList $smart_list)
    {
        $this->authorize('manage', $smart_list);
        $smart_list->load('meals');

        return $this->successResponse('Smart list retrieved successfully', new SmartListResource($smart_list));
    }

    public function update(SmartListRequest $request, SmartList $smart_list)
    {
        $this->authorize('manage', $smart_list);

        $data = $request->validated();
        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }
        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->upload($request->file('image'));
        }

        $smart_list->update($data);
        if ($mealIds !== null) {
            $smart_list->meals()->sync($mealIds);
        }
        $smart_list->load('meals');

        return $this->successResponse('Wish list updated successfully', new SmartListResource($smart_list));
    }

    public function destroy( SmartList $smart_list)
    {
        $this->authorize('manage', $smart_list);

        $smart_list->meals()->detach();
        $smart_list->delete();

        return $this->successResponse('Wish list deleted successfully');
    }

}