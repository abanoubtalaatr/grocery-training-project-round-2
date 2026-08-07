<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\StoreFaqAction;
use App\Action\Api\UpdateFaqAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFaqRequest;
use App\Http\Requests\Api\UpdateFaqRequest;
use App\Http\Resources\Api\FaqResource;
use App\Models\Faq;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Faq::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                    ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        $perPage = $request->get('per_page', 15);
        $faqs = $query->paginate($perPage);

        $categories = null;
        if ($request->boolean('with_categories', false)) {
            $categories = Faq::active()
                ->distinct('category')
                ->pluck('category')
                ->filter()
                ->values();
        }

        $response = [
            'faqs' => FaqResource::collection($faqs),
            'pagination' => [
                'current_page' => $faqs->currentPage(),
                'last_page' => $faqs->lastPage(),
                'per_page' => $faqs->perPage(),
                'total' => $faqs->total(),
                'from' => $faqs->firstItem(),
                'to' => $faqs->lastItem(),
            ],
        ];

        if ($categories !== null) {
            $response['categories'] = $categories;
        }

        return $this->success($response, 'FAQs retrieved successfully');
    }

    public function store(StoreFaqRequest $request, StoreFaqAction $action): JsonResponse
    {
        $faq = $action->execute($request->validated());

        return $this->success(new FaqResource($faq), 'FAQ created successfully', 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        return $this->success(new FaqResource($faq), 'FAQ retrieved successfully');
    }

    public function update(UpdateFaqRequest $request, Faq $faq, UpdateFaqAction $action): JsonResponse
    {
        $action->execute($faq, $request->validated());

        return $this->success(new FaqResource($faq), 'FAQ updated successfully');
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return $this->success(null, 'FAQ deleted successfully');
    }

    public function categories(): JsonResponse
    {
        $categories = Faq::active()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return $this->success($categories, 'FAQ categories retrieved successfully');
    }

    public function byCategory(string $category): JsonResponse
    {
        $faqs = Faq::active()
            ->category($category)
            ->ordered()
            ->get();

        return $this->success(FaqResource::collection($faqs), 'FAQs retrieved successfully');
    }
}
