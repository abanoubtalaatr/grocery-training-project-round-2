<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmartList;
use Illuminate\Http\Request;

class SmartListListController extends Controller
{
    public function index()
    {
        $smartLists = SmartList::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Smart List Lists',
            'data' => $smartLists,
        ]);
    }

    public function show(SmartList $smartList)
    {
        if ($smartList->user_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
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
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Smart List List created',
            'data' => $smartList,
        ], 201);
    }

    public function update(Request $request, SmartList $smartList)
    {
        if ($smartList->user_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $smartList->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Smart List List updated',
            'data' => $smartList,
        ]);
    }

    public function destroy(SmartList $smartList)
    {
        if ($smartList->user_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $smartList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Smart List List deleted',
        ]);
    }
}