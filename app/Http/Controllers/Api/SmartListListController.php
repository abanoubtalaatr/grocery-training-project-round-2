<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmartListListRequest;
use App\Http\Requests\UpdateSmartListListRequest;
use App\Http\Resources\SmartListListResource;
use App\Models\SmartList;
use Illuminate\Support\Facades\Auth;

class SmartListListController extends Controller
{
    public function index()
    {
        $smartLists = Auth::user()->smartLists()->paginate(10);
        
        return response()->json([
            'success' => true,
            'message' => 'Smart list list retrieved successfully',
            'data' => SmartListListResource::collection($smartLists),
        ]);
    }

    public function show(SmartList $smartList)
    {   
        $this->authorize('view', $smartList);

        return response()->json([
            'success' => true,
            'message' => 'Smart list list retrieved successfully',
            'data' => new SmartListListResource($smartList),
        ]);
    }

    public function store(StoreSmartListListRequest $request)
    {
        $smartList = Auth::user()->smartLists()->create($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Smart list list created',
            'data' => new SmartListListResource($smartList),
        ]);
    }

    public function update(UpdateSmartListListRequest $request, SmartList $smartList)
    {
        $this->authorize('update', $smartList);

        $smartList->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Smart list list updated',
            'data' => new SmartListListResource($smartList),
        ]);
    }

    public function destroy(SmartList $smartList)
    {
        $this->authorize('delete', $smartList);

        $smartList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Smart list list deleted',
        ]);
    }

}
