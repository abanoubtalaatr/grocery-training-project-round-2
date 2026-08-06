<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaticPageResource;
use App\Http\Resources\StaticPageCollection;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Action\Api\ListStaticPagesAction;
use App\Action\Api\CreateStaticPageAction;
use App\Action\Api\ShowStaticPageBySlugAction;
use App\Action\Api\UpdateStaticPageAction;
use App\Action\Api\DeleteStaticPageAction;
use App\Action\Api\ImportantPagesAction;

class StaticPageController extends Controller
{
    /**
     * Display a listing of static pages.
     */
    public function index(Request $request, ListStaticPagesAction $action)
    {
        $user = $request->user();

        $pages = $action->execute($request, $user);

        if ($pages instanceof \Illuminate\Contracts\Pagination\Paginator) {
            return new StaticPageCollection($pages);
        }

        // convert to collection response
        return new StaticPageCollection(collect($pages));
    }

    /**
     * Store a newly created static page.
     */
    public function store(Request $request, CreateStaticPageAction $action)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|unique:static_pages,slug|max:100',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'boolean',
            'order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $page = $action->execute($validator->validated());

        return response()->json([
            'message' => 'Page created successfully',
            'data' => new StaticPageResource($page)
        ], 201);
    }

    /**
     * Display the specified static page by slug.
     */
    public function showBySlug($slug, ShowStaticPageBySlugAction $action)
    {
        $page = $action->execute($slug, request()->user());

        if (! $page) {
            return response()->json([
                'message' => 'Page not found'
            ], 404);
        }

        return new StaticPageResource($page);
    }

    /**
     * Display the specified static page by ID.
     */
    public function show(StaticPage $staticPage)
    {
        return new StaticPageResource($staticPage);
    }

    /**
     * Update the specified static page.
     */
    public function update(Request $request, StaticPage $staticPage, UpdateStaticPageAction $action)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'sometimes|required|string|max:100|unique:static_pages,slug,' . $staticPage->id,
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'sometimes|boolean',
            'order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $page = $action->execute($staticPage, $validator->validated());

        return response()->json([
            'message' => 'Page updated successfully',
            'data' => new StaticPageResource($page)
        ]);
    }

    /**
     * Remove the specified static page.
     */
    public function destroy(StaticPage $staticPage, DeleteStaticPageAction $action)
    {
        $action->execute($staticPage);

        return response()->json([
            'message' => 'Page deleted successfully'
        ]);
    }

    /**
     * Get important pages (for footer/menu).
     */
    public function importantPages()
    {
        $pages = StaticPage::published()
            ->whereIn('slug', ['terms-and-conditions', 'policies', 'about-us', 'contact-us'])
            ->ordered()
            ->get(['slug', 'title']);

        return response()->json([
            'data' => $pages
        ]);
    }
}