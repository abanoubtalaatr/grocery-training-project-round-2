<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotMessage;
use App\Models\Faq;
use App\Models\Meal;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotController extends Controller
{
    use \App\Traits\ApiResponse;

    /**
     * Chat with AI (meals, FAQs, orders, payment, offers). Saves history and optional rating.
     */
    public function chat(\App\Http\Requests\Api\ChatbotRequest $request, \App\Action\Api\ChatWithAiAction $action): JsonResponse
    {
        $validated = $request->validated();

        $message = $action->execute($request->user(), $validated);

        return $this->success(new \App\Http\Resources\Api\ChatbotMessageResource($message), 'Chat response generated successfully');
    }


    /**
     * Get current user's chatbot conversation history (paginated).
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
            $messages = $request->user()
                ->chatbotMessages()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $items = $messages->getCollection()->map(function (ChatbotMessage $m) {
                return [
                    'id' => $m->id,
                    'question' => $m->question,
                    'answer' => $m->answer,
                    'rating' => $m->rating,
                    'created_at' => $m->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Chat history retrieved successfully',
                'data' => [
                    'items' => $items,
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'per_page' => $messages->perPage(),
                        'total' => $messages->total(),
                        'from' => $messages->firstItem(),
                        'to' => $messages->lastItem(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot history error', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chat history',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get suggested questions for the chatbot (quick replies).
     */
    public function suggestions(Request $request): JsonResponse
    {
        $locale = $request->input('locale', 'en');
        $isAr = $locale === 'ar';

        $suggestions = $isAr
            ? [
                ['id' => 'faq', 'label' => 'أسئلة شائعة', 'question' => 'ما هي الأسئلة الشائعة؟'],
                ['id' => 'orders', 'label' => 'تتبع الطلب', 'question' => 'كيف أتتبع طلبي؟'],
                ['id' => 'payment', 'label' => 'طرق الدفع', 'question' => 'ما طرق الدفع المتاحة؟'],
                ['id' => 'products', 'label' => 'المنتجات والمفضلة', 'question' => 'ما المنتجات المتاحة والعروض؟'],
                ['id' => 'offers', 'label' => 'كوبونات وعروض', 'question' => 'ما العروض وكوبونات الخصم الحالية؟'],
            ]
            : [
                ['id' => 'faq', 'label' => 'FAQs', 'question' => 'What are the frequently asked questions?'],
                ['id' => 'orders', 'label' => 'Track order', 'question' => 'How do I track my order?'],
                ['id' => 'payment', 'label' => 'Payment methods', 'question' => 'What payment methods do you accept?'],
                ['id' => 'products', 'label' => 'Products & favorites', 'question' => 'What products and offers do you have?'],
                ['id' => 'offers', 'label' => 'Coupons & offers', 'question' => 'What promo codes or offers are available?'],
            ];

        return response()->json([
            'success' => true,
            'message' => 'Suggestions retrieved successfully',
            'data' => [
                'suggestions' => $suggestions,
            ],
        ]);
    }
}
