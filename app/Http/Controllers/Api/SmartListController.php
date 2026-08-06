<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SmartList\DestroySmartListAction;
use App\Actions\Api\SmartList\IndexSmartListAction;
use App\Actions\Api\SmartList\ShowSmartListAction;
use App\Actions\Api\SmartList\StoreSmartListAction;
use App\Actions\Api\SmartList\UpdateSmartListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Traits\ApiTrait;

class SmartListController extends Controller
{
    use ApiTrait;

    public function index(IndexSmartListAction $action)
    {
        return $this->dataResponse(
            SmartListResource::collection(
                $action->run()
            ),
            'Smart lists retrieved successfully'
        );
    }

    public function store(SmartListRequest $request,StoreSmartListAction $action ) {
        return $this->dataResponse(
            new SmartListResource(
                $action->run($request)
            ),
            'Wish list created successfully',
            201
        );
    }

    public function show(int $id, ShowSmartListAction $action) {
        return $this->dataResponse(
            new SmartListResource(
                $action->run($id)
            ),
            'Smart list retrieved successfully'
        );
    }

    public function update(SmartListRequest $request, int $id, UpdateSmartListAction $action) {
        return $this->dataResponse(
            new SmartListResource(
                $action->run($request, $id)
            ),
            'Smart list updated successfully'
        );
    }

    public function destroy(int $id, DestroySmartListAction $action) {
        $action->run($id);
        return $this->successResponse(
            'Smart list deleted successfully'
        );
    }
}