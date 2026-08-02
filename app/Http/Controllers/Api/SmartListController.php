<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Services\SmartListService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    use ApiResponse;

    public function __construct(private SmartListService $smartListService) {}

    public function index(Request $request)
    {
        $smartLists = SmartList::where('user_id', $request->user()->id)->with('meals')->get();

        return $this->successResponse(
            SmartListResource::collection($smartLists),
            'Smart lists retrieved successfully'
        );
    }

    public function store(SmartListRequest $request)
    {
        $smartList = $this->smartListService->create(
            $request->user(), $request->validated(), $request);

        return $this->successResponse(
            new SmartListResource($smartList),
            'Smart list created successfully',
            201
        );
    }

    public function show(Request $request, SmartList $smartList)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->with('meals')->findOrFail($id);

        return $this->successResponse(
            new SmartListResource($smartList),
            'Smart list retrieved successfully'
        );
    }

    public function update(SmartListRequest $request, SmartList $smartList)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);  
        $smartList = $this->smartListService->update(
        $smartList, $request->validated(),$request);

        return $this->successResponse(
            new SmartListResource($smartList),
            'Smart list updated successfully'
        );
    }

    public function destroy(Request $request, SmartList $smartList)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $smartList->meals()->detach();
        $smartList->delete();

        return $this->successResponse(null, 'Smart list deleted successfully');
    }
}
