<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SmartListListController extends Controller
{
    public function index()
    {
        $smartListLists = SmartListList::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Smart List Lists',
            'data' => SmartListList::all(),
        ]);
    }

    public function show(SmartList $smartList)
    {   
        if($smartList->user_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Smart List List',
            'data' => $smartList,
        ]);
    }

    public function store(Request $request)
    {
        $smartList = SmartList::create([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active,
            'is_public' => $request->is_public,
            'is_deleted' => $request->is_deleted,
            'is_archived' => $request->is_archived,
            'is_pinned' => $request->is_pinned,
            'is_favorite' => $request->is_favorite,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Smart List List created',
            'data' => $smartList,
        ]);
    }

    public function update(Request $request, SmartList $smartList)
    {
        if($smartList->user_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function destroy(SmartList $smartList)
    {
        if($smartList->user_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    }

}
