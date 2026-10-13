<?php

namespace Tests\Feature;

use App\Models\ChatbotMessage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebAiAssistantTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        ChatbotMessage::query()->delete();

        $this->user = User::create([
            'username' => 'aiuser_' . Str::random(6),
            'email' => 'ai_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'AI',
            'lastname' => 'User',
        ]);

        config(['services.gemini.api_key' => 'test-key']);
        putenv('GEMINI_API_KEY=test-key');
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    public function test_guest_cannot_view_ai_assistant(): void
    {
        $response = $this->get(route('ai-assistant'));

        $response->assertRedirect();
    }

    public function test_user_can_view_ai_assistant(): void
    {
        $this->login();

        $response = $this->get(route('ai-assistant'));

        $response->assertStatus(200);
        $response->assertSee(trans('ai_assistant.heading'));
        $response->assertSee('AI Assistant');
    }

    public function test_user_can_send_message(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'We have a great selection of organic produce. Check our catalog for fresh fruits and vegetables!'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->login();

        $response = $this->postJson(route('chatbot.chat'), [
            'question' => 'What organic products do you have?',
            'locale' => 'en',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'question' => 'What organic products do you have?',
            ],
        ]);
        $response->assertJsonStructure(['data' => ['id', 'answer']]);

        $this->assertDatabaseHas('chatbot_messages', [
            'user_id' => $this->user->id,
            'question' => 'What organic products do you have?',
        ]);
    }

    public function test_user_can_view_conversation_history(): void
    {
        ChatbotMessage::create([
            'user_id' => $this->user->id,
            'question' => 'What are your opening hours?',
            'answer' => 'We are open from 9 AM to 9 PM daily.',
        ]);

        ChatbotMessage::create([
            'user_id' => $this->user->id,
            'question' => 'Do you deliver?',
            'answer' => 'Yes, we deliver to your doorstep.',
        ]);

        $this->login();

        $response = $this->getJson(route('chatbot.history'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(2, 'data.items');
    }

    public function test_user_can_get_suggestions(): void
    {
        $this->login();

        $response = $this->getJson(route('chatbot.suggestions') . '?locale=en');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(5, 'data.suggestions');
    }

    public function test_suggestions_returns_arabic_when_locale_ar(): void
    {
        $this->login();

        $response = $this->getJson(route('chatbot.suggestions') . '?locale=ar');

        $response->assertStatus(200);
        $response->assertJsonPath('data.suggestions.0.id', 'faq');

        $data = $response->json('data.suggestions');
        $questionTexts = array_column($data, 'question');
        $this->assertContains('كيف أتتبع طلبي؟', $questionTexts);
    }

    public function test_user_can_request_human_handoff(): void
    {
        $this->login();

        $response = $this->postJson(route('chatbot.handoff'), [
            'question' => 'I need help with my order',
            'notes' => 'My order is late',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure(['data' => ['support_message_id', 'status']]);

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => $this->user->id,
            'subject' => 'AI Support Handoff',
            'status' => 'new',
        ]);
    }

    public function test_user_can_handoff_with_order_id(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-HANDOFF-' . strtoupper(uniqid()),
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'placed',
            'subtotal' => 25.00,
            'tax' => 2.50,
            'discount' => 0,
            'shipping_fee' => 5.00,
            'total' => 32.50,
            'placed_at' => now(),
        ]);

        $this->login();

        $response = $this->postJson(route('chatbot.handoff'), [
            'question' => 'Where is my order?',
            'order_id' => $order->id,
            'notes' => 'It was supposed to arrive yesterday',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => $this->user->id,
            'subject' => "AI Support Handoff (Order #{$order->id})",
        ]);
    }

    public function test_user_conversations_are_isolated(): void
    {
        $otherUser = User::create([
            'username' => 'otherai_' . Str::random(6),
            'email' => 'otherai_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'AI',
        ]);

        ChatbotMessage::create([
            'user_id' => $otherUser->id,
            'question' => 'Private question',
            'answer' => 'Private answer',
        ]);

        $this->login();

        $response = $this->getJson(route('chatbot.history'));

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data.items');
    }

    public function test_chat_requires_authentication(): void
    {
        $response = $this->postJson(route('chatbot.chat'), [
            'question' => 'Hello',
        ]);

        $response->assertStatus(401);
    }

    public function test_chat_validates_question_required(): void
    {
        $this->login();

        $response = $this->postJson(route('chatbot.chat'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['question']);
    }

    public function test_chat_rejects_empty_question(): void
    {
        $this->login();

        $response = $this->postJson(route('chatbot.chat'), [
            'question' => '   ',
        ]);

        $response->assertStatus(422);
    }

    public function test_ai_assistant_page_includes_csrf_token(): void
    {
        $this->login();

        $response = $this->get(route('ai-assistant'));

        $response->assertStatus(200);
        $response->assertSee('csrf-token');
    }

    public function test_chat_returns_error_when_api_key_missing(): void
    {
        putenv('GEMINI_API_KEY=');
        config(['services.gemini.api_key' => null]);

        $this->login();

        $response = $this->postJson(route('chatbot.chat'), [
            'question' => 'Hello',
        ]);

        $response->assertStatus(503);
    }
}
