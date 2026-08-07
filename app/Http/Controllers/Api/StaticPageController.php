<?php

namespace App\Http\Controllers\Api;

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

    /**
     * Get all published static pages with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = StaticPage::query();

        if (! $request->user() || ! $request->user()->is_admin) {
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

        return $this->paginated($pages, 'Pages retrieved successfully');
    }

    /**
     * Get single page by ID
     */
    public function show(StaticPage $staticPage): JsonResponse
    {
        return $this->success(
            new StaticPageResource($staticPage),
            'Page retrieved successfully'
        );
    }

    /**
     * Create new static page (admin only)
     */
    public function store(StoreStaticPageRequest $request): JsonResponse
    {
        $this->authorize('create', StaticPage::class);

        $page = StaticPage::create($request->validated());

        return $this->success(
            new StaticPageResource($page),
            'Page created successfully',
            201
        );
    }

    /**
     * Update static page (admin only)
     */
    public function update(UpdateStaticPageRequest $request, StaticPage $staticPage): JsonResponse
    {
        $this->authorize('update', $staticPage);

        $staticPage->update($request->validated());

        return $this->success(
            new StaticPageResource($staticPage),
            'Page updated successfully'
        );
    }

    /**
     * Delete static page (admin only)
     */
    public function destroy(StaticPage $staticPage): JsonResponse
    {
        $this->authorize('delete', $staticPage);

        $staticPage->delete();

        return $this->success(null, 'Page deleted successfully');
    }
}
