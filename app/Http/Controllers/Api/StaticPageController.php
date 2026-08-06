<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\CreateStaticPageAction;
use App\Action\Api\GetImportantPagesAction;
use App\Action\Api\UpdateStaticPageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreStaticPageRequest;
use App\Http\Requests\Api\UpdateStaticPageRequest;
use App\Http\Resources\Api\StaticPageResource;
use App\Models\StaticPage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = StaticPage::query();

        if (!$request->user() || !$request->user()->is_admin) {
            $query->published();
        }

        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        $perPage = $request->get('per_page', 20);
        $pages = $query->paginate($perPage);

        return $this->success(StaticPageResource::collection($pages),'Static pages retrieved successfully');
    }

    public function store(StoreStaticPageRequest $request, CreateStaticPageAction $action): JsonResponse
    {
        $page = $action->execute($request->validated());

        return $this->success(new StaticPageResource($page),'Page created successfully',201);
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $page = StaticPage::bySlug($slug)->first();

        if (!$page) {
            abort(404, 'Page not found');
        }

        if (!$page->is_published && (!request()->user() || !request()->user()->is_admin)) {
            abort(404, 'Page not found');
        }

        return $this->success(new StaticPageResource($page),'Static page retrieved successfully');
    }

    public function show(StaticPage $staticPage): JsonResponse
    {
        return $this->success(new StaticPageResource($staticPage),'Static page retrieved successfully');
    }

    public function update(UpdateStaticPageRequest $request, StaticPage $staticPage, UpdateStaticPageAction $action): JsonResponse
    {
        $action->execute($staticPage, $request->validated());

        return $this->success(new StaticPageResource($staticPage),'Page updated successfully');
    }

    public function destroy(StaticPage $staticPage): JsonResponse
    {
        $staticPage->delete();

        return $this->success(null, 'Page deleted successfully');
    }

    public function importantPages(GetImportantPagesAction $action): JsonResponse
    {
        $pages = $action->execute();

        return $this->success($pages, 'Important pages retrieved successfully');
    }
}
