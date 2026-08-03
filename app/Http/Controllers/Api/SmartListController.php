<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Requests\StoreSmartListRequest;
use App\Http\Requests\UpdateSmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Services\MediaUploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SmartListController extends Controller
{
    use ApiResponse;
    protected MediaUploadService $mediaService;
    public function __construct(MediaUploadService $mediaService)
    {
        // $this->me;
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

        $smartList = SmartList::where('user_id', $request->user()->id)->with('meals')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Smart list retrieved successfully',
            'data' => new SmartListResource($smartList),
        ]);
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

   
}
