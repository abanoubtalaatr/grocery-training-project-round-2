<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmartList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class SmartListListController extends Controller
{
    public function index()
    {
        $smartList = SmartList::all();

        return response()->json([
            'success' => true,
            'message' => 'Smart List Lists',
            'data' => $smartList,
        ]);
    }

    public function show(SmartList $smartList)
    {
        if ($smartList->user_id !== auth()->user()->id) {
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
        if ($smartList->user_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'is_deleted' => 'boolean',
            'is_archived' => 'boolean',
            'is_pinned' => 'boolean',
            'is_favorite' => 'boolean',
        ]);   // or we can make validation like a class in request folder and use it here
        $smartList->update($validatedData);

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
            ], 401);
        }

        $smartList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Smart List List deleted successfully',
        ]);
    }
}
