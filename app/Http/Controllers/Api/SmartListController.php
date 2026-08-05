<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Http\Requests\StoreSmartListRequest;
use App\Http\Requests\UpdateSmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Services\MediaUploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SmartListController extends Controller
{
    use ApiResponse;
    protected MediaUploadService $mediaService;
    public function __construct(MediaUploadService $mediaService)
    {
         $this->mediaService=$mediaService;
    }
    public function index(Request $request)
    {
        $this->authorize('viewAny', SmartList::class);
        $smartLists = $request->user()
            ->smartLists()
            ->with('meals:id,price,name,image')
            ->latest()
            ->paginate(15);
        return $this->successResponse(
            SmartListResource::collection($smartLists)->response()->getData(true),
            'Smart lists retrieved successfully'
        );
    }
    public function store(StoreSmartListRequest $request)
    {


        $data = $request->validated();


        if ($request->hasFile('image')) {
            $data['image'] = $this->mediaService->updload_file($request->file('image'), 'smart_list');
        }
        $smartList = DB::transaction(function () use ($request, $data) {
            $smartList = $request->user()->smartLists()->create($data);
            if (!empty($data['meal_ids'])) {
                $smartList->meals()->attach($data['meal_ids']);
            }
            return $smartList;
        });
        $smartList->load('meals:id,name,price,image');
        return $this->successResponse(
            new SmartListResource($smartList),
            'Smart list created successfully',
            Response::HTTP_CREATED
        );
    }

   public function show(Request $request, SmartList $smartList)
    {
        $this->authorize('view', $smartList);

        $smartList->load('meals:id,name,price,image');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Smart list retrieved successfully'
        );
    }
    public function update(UpdateSmartListRequest $request, SmartList $smartList)
    {

        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $this->mediaService->replace_image($request->file('image'), $smartList->image, 'smart_list');
        }
        DB::transaction(function () use ($data, $smartList) {
            $smartList->update($data);
            if (array_key_exists('meal_ids', $data)) {
                $smartList->meals()->sync($data['meal_ids'] ?? []);
            }
        });
        $smartList->load('meals:id,name,price,image');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Smart list updated successfully'
        );
    }

    public function destroy(Request $request, SmartList $smartList)
    {
        $this->authorize('delete', $smartList);

        if ($smartList->image) {
            $this->mediaService->delete_image($smartList->image);
        }

        $smartList->delete();

        return $this->successResponse(
            null,
            'Smart list deleted successfully'
        );
    }
public function addMeal(Request $request, SmartList $smartList)
    {
        $this->authorize('update', $smartList);

        $validated = $request->validate([
            'meal_id' => ['required', 'integer', 'exists:meals,id'],
        ]);

        $smartList->meals()->syncWithoutDetaching([$validated['meal_id']]);
        $smartList->load('meals:id,name,price,image');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Meal added successfully'
        );
    }

    /**
     * Remove a meal from a smart list.
     */
    public function removeMeal(Request $request, SmartList $smartList, int $mealId)
    {
        $this->authorize('update', $smartList);

        $smartList->meals()->detach($mealId);
        $smartList->load('meals:id,name,price,image');

        return $this->successResponse(
            new SmartListResource($smartList),
            'Meal removed successfully'
        );
    }
   
}
