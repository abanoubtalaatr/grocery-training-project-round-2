<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmartListListRequest;
use App\Http\Requests\UpdateSmartListListRequest;
use App\Http\Resources\SmartListListResource;
use App\Models\SmartListList;
use Illuminate\Support\Facades\Auth;

class SmartListListController extends Controller
{
    public function index()
    {
        $smartLists = Auth::user()->smartListLists()->paginate(10);
        
        return response()->json([
            'success' => true,
            'message' => 'Smart list list retrieved successfully',
            'data' => SmartListListResource::collection($smartLists),
        ]);
    }

    public function show(SmartListList $smartListList)
    {   
        $this->authorize('view', $smartListList);

        return response()->json([
            'success' => true,
            'message' => 'Smart list list retrieved successfully',
            'data' => new SmartListListResource($smartListList),
        ]);
    }

    public function store(StoreSmartListListRequest $request)
    {
        $smartList = Auth::user()->smartListLists()->create($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Smart list list created',
            'data' => new SmartListListResource($smartList),
        ]);
    }

    public function update(UpdateSmartListListRequest $request, SmartListList $smartListList)
    {
        $this->authorize('update', $smartListList);

        $smartListList->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Smart list list updated',
            'data' => new SmartListListResource($smartListList),
        ]);
    }

    public function destroy(SmartListList $smartListList)
    {
        $this->authorize('delete', $smartListList);

        $smartListList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Smart list list deleted',
        ]);
    }

}
