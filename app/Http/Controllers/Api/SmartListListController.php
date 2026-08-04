<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Traits\ApiResponseTrait;
use App\Traits\HandlesImageUploads;
use Illuminate\Support\Facades\Storage;

class SmartListListController extends Controller
{
    use ApiResponseTrait, HandlesImageUploads;

    public function index()
    {
        $smartLists = SmartList::where('user_id', auth()->id())->get();

        return $this->successResponse(
            'Smart lists retrieved successfully',
            SmartListResource::collection($smartLists)
        );
    }

    public function show(SmartList $smartList)
    {
        $this->authorize('view', $smartList);

        return $this->successResponse(
            'Smart list retrieved successfully',
            new SmartListResource($smartList->load('meals'))
        );
    }

    public function store(SmartListRequest $request)
    {
        $this->authorize('create', SmartList::class);

        $smartList = SmartList::create([
            ...$request->safe()->except(['image', 'meal_ids']),
            'user_id' => auth()->id(),
        ]);

        if ($request->hasFile('image')) {
            $smartList->update([
                'image' => $this->storeImage($request->file('image'), 'smart-lists'),
            ]);
        }

        if ($request->filled('meal_ids')) {
            $smartList->meals()->sync($request->input('meal_ids'));
        }

        return $this->successResponse(
            'Smart list created successfully.',
            new SmartListResource($smartList->fresh('meals')),
            201
        );
    }

    public function update(SmartListRequest $request, SmartList $smartList)
    {
        $this->authorize('update', $smartList);

        $smartList->update($request->safe()->except(['image', 'meal_ids']));

        if ($request->hasFile('image')) {
            $smartList->update([
                'image' => $this->storeImage($request->file('image'), 'smart-lists', $smartList->image),
            ]);
        }

        if ($request->has('meal_ids')) {
            $smartList->meals()->sync($request->input('meal_ids', []));
        }

        return $this->successResponse(
            'Smart list updated successfully.',
            new SmartListResource($smartList->fresh('meals'))
        );
    }

    public function destroy(SmartList $smartList)
    {
        $this->authorize('delete', $smartList);

        if ($smartList->image) {
            Storage::disk('public')->delete($smartList->image);
        }

        $smartList->delete();

        return $this->successResponse('Smart list deleted successfully.');
    }
}