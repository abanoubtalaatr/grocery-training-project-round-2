<?php

namespace App\Actions\Chatbot;

use App\Models\Faq;
use App\Models\Meal;
use App\Models\Offer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatAction
{
    public function execute($user, array $data)
    {
        $apiKey = env('GEMINI_API_KEY');

        $meals = Meal::with(['category', 'subcategory'])
            ->available()
            ->orderByDesc('sold_count')
            ->limit(250)
            ->get();

        $faqs = Faq::active()
            ->ordered()
            ->get(['question', 'answer', 'category']);

        $offers = Offer::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->get();

        $prompt = $this->buildPrompt(
            $data['question'],
            $data['locale'] ?? null,
            $meals,
            $faqs,
            $offers
        );

        $response = Http::timeout(30)->post(
            'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key='.$apiKey,
            [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt,
                            ],
                        ],
                    ],
                ],
            ]
        );

        if (! $response->successful()) {
            Log::error($response->body());

            throw new \Exception('Gemini request failed');
        }

        $answer = $this->extractText($response->json());

        return $user->chatbotMessages()->create([
            'question' => $data['question'],
            'answer' => $answer,
            'rating' => $data['rating'] ?? null,
        ]);
    }

    private function buildPrompt($question, $locale, $meals, $faqs, $offers)
    {
        return "
Question:
{$question}

Meals:
".$meals->toJson()."

FAQs:
".$faqs->toJson()."

Offers:
".$offers->toJson();
    }

    private function extractText(array $response)
    {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}