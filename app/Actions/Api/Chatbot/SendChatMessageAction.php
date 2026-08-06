<?php

namespace App\Actions\Api\Chatbot;

use App\Models\Faq;
use App\Models\Meal;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendChatMessageAction
{
    public function run(User $user, string $question, ?int $rating = null, ?string $locale = null): array
    {
        $apiKey = env('GEMINI_API_KEY');

        if (! $apiKey) {
            throw new RuntimeException('Gemini API key is not configured', 503);
        }

        $meals = Meal::with(['category', 'subcategory'])
            ->available()
            ->orderByDesc('sold_count')
            ->limit(250)
            ->get()
            ->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'description' => $meal->description,
                    ...$meal->getApiPriceAttributes(),
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,
                    'size' => $meal->size,
                    'brand' => $meal->brand,
                    'category' => $meal->category?->name,
                    'subcategory' => $meal->subcategory?->name,
                    'is_featured' => $meal->is_featured,
                    'stock_quantity' => $meal->stock_quantity,
                    'in_stock' => $meal->isInStock(),
                    'offer_title' => $meal->offer_title,
                    'has_offer' => $meal->hasOffer(),
                ];
            });

        $faqs = Faq::active()->ordered()->get(['question', 'answer', 'category'])->toArray();
        $offers = Offer::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->get(['title', 'code', 'description', 'type', 'discount_value', 'minimum_purchase'])
            ->toArray();

        $localeHint = $this->getLocaleHint($locale);

        $prompt = 'You are a helpful assistant for a grocery/meal delivery app. '.$localeHint."\n\n";

        $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;
        $prompt .= "## Available meals (menu)\n".json_encode($meals, $jsonFlags)."\n\n";

        if (! empty($faqs)) {
            $prompt .= "## FAQ (use these to answer general questions)\n".json_encode($faqs, $jsonFlags)."\n\n";
        }
        if (! empty($offers)) {
            $prompt .= "## Active offers / promo codes\n".json_encode($offers, $jsonFlags)."\n\n";
        }

        $prompt .= "## Guidelines\n";
        $prompt .= "- For order status / track order: explain that the user can go to 'My Orders' or 'Track Order' in the app to see status. Do not invent order IDs.\n";
        $prompt .= "- For payment: we support card and cash on delivery; guide them to checkout or payment settings.\n";
        $prompt .= "- For products, favorites, smart lists: use the meals data above; you can suggest categories or featured items.\n";
        $prompt .= "- For coupons: use the active offers list; mention code and conditions if relevant.\n";
        $prompt .= 'User question: '.$question."\n\n";
        $prompt .= 'Provide a helpful, concise answer. If the question is off-topic, politely redirect to app features (orders, meals, offers, FAQ).';

        $response = Http::timeout(30)->post(
            'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key='.$apiKey,
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ],
            ]
        );

        if (! $response->successful()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException($response->status() === 401 ? 'Invalid API key' : 'API request failed', 502);
        }

        $responseData = $response->json() ?? [];
        $aiResponse = $this->extractGeminiText($responseData);

        if ($aiResponse === null || $aiResponse === '') {
            Log::warning('Gemini returned no text candidate', ['body' => $response->body()]);

            throw new RuntimeException($responseData['promptFeedback']['blockReason'] ?? 'empty_or_blocked', 502);
        }

        $answer = trim($aiResponse);

        $message = $user->chatbotMessages()->create([
            'question' => $question,
            'answer' => $answer,
            'rating' => $rating,
        ]);

        return [
            'id' => $message->id,
            'question' => $question,
            'answer' => $answer,
            'rating' => $message->rating,
            'meals_count' => $meals->count(),
        ];
    }

    private function extractGeminiText(array $responseData): ?string
    {
        $candidates = $responseData['candidates'] ?? [];
        if (! is_array($candidates)) {
            return null;
        }

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }
            $parts = $candidate['content']['parts'] ?? null;
            if (! is_array($parts)) {
                continue;
            }
            foreach ($parts as $part) {
                if (is_array($part) && isset($part['text']) && is_string($part['text']) && $part['text'] !== '') {
                    return $part['text'];
                }
            }
        }

        return null;
    }

    private function getLocaleHint(?string $locale): string
    {
        if ($locale === 'ar') {
            return 'Respond in Arabic (العربية) unless the user wrote in English.';
        }
        if ($locale === 'en') {
            return 'Respond in English.';
        }

        return 'Respond in the same language the user used.';
    }
}
