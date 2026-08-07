<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqCollection;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FaqController extends Controller
{
    public function __construct(
        protected FaqService $faqService
    ) {}

    /**
     * Display a listing of the FAQs.
     */
    public function index(Request $request): JsonResponse
    {
        $faqs = $this->faqService->getFilteredFaqs($request->all());

        $response = [
            'data' => new FaqCollection($faqs),
        ];

        if ($request->boolean('with_categories', false)) {
            $response['categories'] = $this->faqService->getCategories();
        }

        return response()->json($response);
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(StoreFaqRequest $request): JsonResponse
    {
        $faq = $this->faqService->createFaq($request->validated());

        return response()->json([
            'message' => 'FAQ created successfully',
            'data' => new FaqResource($faq),
        ], 201);
    }

    /**
     * Display the specified FAQ.
     */
    public function show(Faq $faq): FaqResource
    {
        return new FaqResource($faq);
    }

    /**
     * Update the specified FAQ.
     */
    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $updatedFaq = $this->faqService->updateFaq($faq, $request->validated());

        return response()->json([
            'message' => 'FAQ updated successfully',
            'data' => new FaqResource($updatedFaq),
        ]);
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq): JsonResponse
    {
        $this->faqService->deleteFaq($faq);

        return response()->json([
            'message' => 'FAQ deleted successfully',
        ]);
    }

    /**
     * Get all FAQ categories.
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'data' => $this->faqService->getCategories(),
        ]);
    }

    /**
     * Get FAQs by category.
     */
    public function byCategory(string $category): AnonymousResourceCollection
    {
        $faqs = $this->faqService->getByCategory($category);

        return FaqResource::collection($faqs);
    }
}