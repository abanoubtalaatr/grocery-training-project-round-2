<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreStaticPageRequest;
use App\Http\Requests\Api\UpdateStaticPageRequest;
use App\Http\Resources\StaticPageCollection;
use App\Http\Resources\StaticPageResource;
use App\Models\StaticPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function index(Request $request): StaticPageCollection
    {
        $query = StaticPage::query()
            ->when(!$request->user() || !$request->user()->is_admin, fn ($query) => $query->published())
            ->when($request->has('published'), fn ($query) => $query->where('is_published', $request->boolean('published')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%");
                });
            })
            ->ordered();

        return new StaticPageCollection($query->paginate($request->integer('per_page', 20)));
    }

    public function store(StoreStaticPageRequest $request): JsonResponse
    {
        $page = StaticPage::create($request->validated());

        return $this->successResponse(new StaticPageResource($page), 'Page created successfully', 201);
    }

    public function showBySlug(Request $request, string $slug): JsonResponse
    {
        $page = StaticPage::bySlug($slug)->first();

        if (!$page || (!$page->is_published && (!$request->user() || !$request->user()->is_admin))) {
            return $this->errorResponse('Page not found', 404);
        }

        return $this->successResponse(new StaticPageResource($page), 'Page retrieved successfully');
    }

    public function show(StaticPage $staticPage): JsonResponse
    {
        return $this->successResponse(new StaticPageResource($staticPage), 'Page retrieved successfully');
    }

    public function update(UpdateStaticPageRequest $request, StaticPage $staticPage): JsonResponse
    {
        $staticPage->update($request->validated());

        return $this->successResponse(new StaticPageResource($staticPage), 'Page updated successfully');
    }

    public function destroy(StaticPage $staticPage): JsonResponse
    {
        $staticPage->delete();

        return $this->successResponse(null, 'Page deleted successfully');
    }

    public function importantPages(): JsonResponse
    {
        $pages = StaticPage::published()
            ->whereIn('slug', ['terms-and-conditions', 'policies', 'about-us', 'contact-us'])
            ->ordered()
            ->get(['slug', 'title']);

        return $this->successResponse($pages, 'Important pages retrieved successfully');
    }
}
