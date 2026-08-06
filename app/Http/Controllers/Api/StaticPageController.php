<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\StaticPage\DestroyStaticPageAction;
use App\Actions\Api\StaticPage\GetStaticPagesAction;
use App\Actions\Api\StaticPage\StoreStaticPageAction;
use App\Actions\Api\StaticPage\UpdateStaticPageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreStaticPageRequest;
use App\Http\Requests\Api\UpdateStaticPageRequest;
use App\Http\Resources\StaticPageCollection;
use App\Http\Resources\StaticPageResource;
use App\Models\StaticPage;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    use ApiTrait;

    public function index(Request $request, GetStaticPagesAction $action)
    {
        $pages = $action->run($request);

        return new StaticPageCollection($pages);
    }

    public function store(StoreStaticPageRequest $request, StoreStaticPageAction $action): JsonResponse
    {
        $page = $action->run($request->validated());

        return $this->dataResponse(
            new StaticPageResource($page),
            'Page created successfully',
            201
        );
    }

    public function show(StaticPage $staticPage): JsonResponse
    {
        return $this->dataResponse(new StaticPageResource($staticPage));
    }

    public function update(UpdateStaticPageRequest $request, StaticPage $staticPage, UpdateStaticPageAction $action): JsonResponse
    {
        $staticPage = $action->run($staticPage, $request->validated());

        return $this->dataResponse(
            new StaticPageResource($staticPage),
            'Page updated successfully'
        );
    }

    public function destroy(StaticPage $staticPage, DestroyStaticPageAction $action): JsonResponse
    {
        $action->run($staticPage);

        return $this->successResponse('Page deleted successfully');
    }
}