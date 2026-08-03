<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Traits\media;
use App\Models\SmartList;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartListController extends Controller
{
    use ApiTrait, media;

    public function index(Request $request)
    {
       $smartLists = SmartList::where('user_id', Auth::id())->with('meals')->get();
        return $this->dataResponse(SmartListResource::collection($smartLists),'Smart lists retrieved successfully');
    }

    public function store(SmartListRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['description'] = $data['description'] ?? '';
        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);
        if ($request->hasFile('image')) {
            $imageName = $this->uploadPhoto($request->file('image'),'smart-lists');
            $data['image'] = $imageName;
        }
        $smartList = SmartList::create($data);
        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }
        $smartList->load('meals');
        return $this->dataResponse(new SmartListResource($smartList), 'Wish list created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $smartList = SmartList::where('user_id', Auth::id())->with('meals')->findOrFail($id);
        return $this->dataResponse(new SmartListResource($smartList), 'Smart list retrieved successfully');
    }
    public function update(Request $request, $id)
    {
        dd(123);
        $smartList = SmartList::where('user_id', Auth::id())->findOrFail($id);
        $data = $request->validated();
        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }
        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {  
            $this->deletePhoto('images/smart-lists/'.$smartList->image);
            $imageName = $this->uploadPhoto($request->file('image'),'smart-lists');
            $data['image'] = $imageName;
        }
        $smartList->update($data);
        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }
        $smartList->load('meals');

        return $this->dataResponse(new SmartListResource($smartList), 'Smart list updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $smartList = SmartList::where('user_id', Auth::id())->findOrFail($id);
        $smartList->meals()->detach();
        $smartList->delete();
        $this->deletePhoto('images/smart-lists/'.$smartList->image);

        return $this->successResponse('Smart list deleted successfully');
    }

}
