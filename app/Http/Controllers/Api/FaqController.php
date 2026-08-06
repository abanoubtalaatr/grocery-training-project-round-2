<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Http\Resources\FaqCollection;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     */
    public function index(Request $request, \App\Action\Api\ListFaqsAction $action)
    {
        $params = [
            'category' => $request->get('category'),
            'active_only' => $request->boolean('active_only', true),
            'search' => $request->get('search'),
        ];

        $perPage = (int) $request->get('per_page', 15);
        $faqs = $action->execute($params, $perPage);

        $response = [
            'data' => new FaqCollection($faqs),
        ];

        if ($request->boolean('with_categories', false)) {
            $categories = Faq::active()
                ->distinct('category')
                ->pluck('category')
                ->filter()
                ->values();

            $response['categories'] = $categories;
        }

        return response()->json($response);
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(\App\Http\Requests\Api\FaqStoreRequest $request, \App\Action\Api\CreateFaqAction $action)
    {
        $data = $request->validated();

        $faq = $action->execute($data);

        return response()->json([
            'message' => 'FAQ created successfully',
            'data' => new FaqResource($faq),
        ], 201);
    }

    /**
     * Display the specified FAQ.
     */
    public function show(Faq $faq)
    {
        return new FaqResource($faq);
    }

    /**
     * Update the specified FAQ.
     */
    public function update(\App\Http\Requests\Api\FaqUpdateRequest $request, Faq $faq, \App\Action\Api\UpdateFaqAction $action)
    {
        $data = $request->validated();

        $faq = $action->execute($faq, $data);

        return response()->json([
            'message' => 'FAQ updated successfully',
            'data' => new FaqResource($faq),
        ]);
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq, \App\Action\Api\DeleteFaqAction $action)
    {
        $action->execute($faq);

        return response()->json([
            'message' => 'FAQ deleted successfully',
        ]);
    }

    /**
     * Get all FAQ categories.
     */
    public function categories()
    {
        $categories = Faq::active()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get FAQs by category.
     */
    public function byCategory($category)
    {
        $faqs = Faq::active()
            ->category($category)
            ->ordered()
            ->get();

        return FaqResource::collection($faqs);
    }
}
