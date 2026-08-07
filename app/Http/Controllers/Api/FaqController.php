<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFaqRequest;
use App\Http\Requests\Api\UpdateFaqRequest;
use App\Http\Resources\FaqCollection;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Faq::query()
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
            ->when($request->boolean('active_only', true), fn ($query) => $query->active())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('question', 'LIKE', "%{$search}%")
                        ->orWhere('answer', 'LIKE', "%{$search}%");
                });
            })
            ->ordered();

        $faqs = $query->paginate($request->integer('per_page', 15));
        $extra = [];

        if ($request->boolean('with_categories', false)) {
            $extra['categories'] = Faq::active()
                ->distinct('category')
                ->pluck('category')
                ->filter()
                ->values();
        }

        return $this->successResponse(
            new FaqCollection($faqs),
            'FAQs retrieved successfully',
            200,
            $extra
        );
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $faq = Faq::create($request->validated());

        return $this->successResponse(new FaqResource($faq), 'FAQ created successfully', 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        return $this->successResponse(new FaqResource($faq), 'FAQ retrieved successfully');
    }

    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $faq->update($request->validated());

        return $this->successResponse(new FaqResource($faq), 'FAQ updated successfully');
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return $this->successResponse(null, 'FAQ deleted successfully');
    }

    public function categories(): JsonResponse
    {
        $categories = Faq::active()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return $this->successResponse($categories, 'FAQ categories retrieved successfully');
    }

    public function byCategory(string $category): JsonResponse
    {
        $faqs = Faq::active()
            ->category($category)
            ->ordered()
            ->get();

        return $this->successResponse(FaqResource::collection($faqs), 'FAQs retrieved successfully');
    }
}
