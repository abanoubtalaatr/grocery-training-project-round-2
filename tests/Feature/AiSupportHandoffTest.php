<?php

namespace Tests\Feature;

use App\Models\ChatbotMessage;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AiSupportHandoffTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('GEMINI_API_KEY=test-fake-key');
        config(['services.gemini.api_key' => 'test-fake-key']);
    }

    /**
     * Test AI to Human Support handoff workflow.
     */
    public function test_ai_chatbot_to_human_support_handoff(): void
    {
        $customer = User::create([
            'name' => 'Chatbot User',
            'username' => 'chatuser_' . time() . rand(10, 99),
            'email' => 'chatuser_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $supportAgent = User::create([
            'name' => 'Handoff Agent',
            'username' => 'handoffagent_' . time() . rand(10, 99),
            'email' => 'handoffagent_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_SUPPORT_AGENT,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'processing',
            'subtotal' => 25.00,
            'tax' => 2.50,
            'discount' => 0.00,
            'shipping_fee' => 5.00,
            'total' => 32.50,
            'placed_at' => now(),
        ]);

        // Create some chatbot message history for this customer
        ChatbotMessage::create([
            'user_id' => $customer->id,
            'question' => 'Where is my order?',
            'answer' => 'You can check your order status in the track orders screen.',
        ]);

        ChatbotMessage::create([
            'user_id' => $customer->id,
            'question' => 'I received the wrong item in my package',
            'answer' => 'I am an AI assistant and cannot inspect physical packages. Let me connect you with a representative.',
        ]);

        // Customer calls handoff endpoint
        $this->actingAs($customer, 'sanctum');
        $handoffResp = $this->postJson('/api/chatbot/handoff', [
            'question' => 'I need a human agent to replace my damaged grocery items.',
            'order_id' => $order->id,
            'notes' => 'Damaged organic apples',
        ]);

        $handoffResp->assertStatus(201);
        $supportMessageId = $handoffResp->json('data.support_message_id');
        $this->assertNotNull($supportMessageId);

        // Verify contact message was created and includes chatbot transcript
        $contactMessage = ContactMessage::findOrFail($supportMessageId);
        $this->assertSame($customer->id, $contactMessage->user_id);
        $this->assertStringContainsString('AI Support Handoff', $contactMessage->subject);
        $this->assertStringContainsString('Damaged organic apples', $contactMessage->message);
        $this->assertStringContainsString('Where is my order?', $contactMessage->message);

        // Support Agent views queue and finds the handoff inquiry
        $this->actingAs($supportAgent, 'sanctum');
        $queueResp = $this->getJson('/api/support/messages');
        $queueResp->assertStatus(200);

        $agentItems = $queueResp->json('data');
        $found = collect($agentItems)->firstWhere('id', $supportMessageId);
        $this->assertNotNull($found);

        // Agent replies to the escalated inquiry
        $replyResp = $this->postJson("/api/support/messages/{$supportMessageId}/reply", [
            'reply' => 'We sincerely apologize for the inconvenience. A replacement package has been dispatched.',
        ]);
        $replyResp->assertStatus(200)
            ->assertJsonPath('data.status', 'replied');

        $contactMessage->refresh();
        $this->assertSame('replied', $contactMessage->status);
        $this->assertSame($supportAgent->id, $contactMessage->replied_by);

        // Customer verifies the reply is visible in their support history
        $this->actingAs($customer, 'sanctum');
        $customerHistory = $this->getJson('/api/support/my-messages');
        $customerHistory->assertStatus(200);

        $myItems = $customerHistory->json('data');
        $myMessage = collect($myItems)->firstWhere('id', $supportMessageId);
        $this->assertNotNull($myMessage);
        $this->assertSame('replied', $myMessage['status']);
        $this->assertStringContainsString('A replacement package has been dispatched', $myMessage['reply']);
    }

    public function test_ai_api_failure_auto_creates_handoff_and_notifies_admin(): void
    {
        config(['mail.admin_email' => 'admin@example.com']);
        Mail::fake();

        $customer = User::create([
            'name' => 'Fail User',
            'username' => 'failuser_' . time() . rand(10, 99),
            'email' => 'failuser_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Invalid key'],
            ], 401),
        ]);

        $this->actingAs($customer, 'web');
        $response = $this->postJson(route('chatbot.chat'), [
            'question' => 'What is my order status?',
        ]);

        $response->assertStatus(502);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure(['support_message_id']);

        $supportMessageId = $response->json('support_message_id');
        $this->assertNotNull($supportMessageId);

        $contactMessage = ContactMessage::findOrFail($supportMessageId);
        $this->assertSame($customer->id, $contactMessage->user_id);
        $this->assertSame('AI Support Handoff — AI Failure', $contactMessage->subject);
        $this->assertStringContainsString('What is my order status?', $contactMessage->message);
        $this->assertSame('new', $contactMessage->status);

        Mail::assertSent(\App\Mail\ContactMessageReceived::class, function ($mail) use ($contactMessage) {
            return $mail->hasTo('admin@example.com')
                && $mail->contactMessage->id === $contactMessage->id;
        });
    }

    public function test_ai_empty_response_auto_creates_handoff(): void
    {
        config(['mail.admin_email' => 'admin@example.com']);
        Mail::fake();

        $customer = User::create([
            'name' => 'Empty User',
            'username' => 'emptyuser_' . time() . rand(10, 99),
            'email' => 'emptyuser_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [],
                'promptFeedback' => ['blockReason' => 'SAFETY'],
            ], 200),
        ]);

        $this->actingAs($customer, 'web');
        $response = $this->postJson(route('chatbot.chat'), [
            'question' => 'Tell me about sensitive topic',
        ]);

        $response->assertStatus(502);
        $response->assertJsonPath('error', 'SAFETY');
        $response->assertJsonStructure(['support_message_id']);

        $supportMessageId = $response->json('support_message_id');
        $this->assertNotNull($supportMessageId);

        $contactMessage = ContactMessage::findOrFail($supportMessageId);
        $this->assertSame('AI Support Handoff — AI Failure', $contactMessage->subject);
        $this->assertStringContainsString('SAFETY', $contactMessage->message);
    }

    public function test_ai_exception_auto_creates_handoff(): void
    {
        config(['mail.admin_email' => 'admin@example.com']);
        Mail::fake();

        $customer = User::create([
            'name' => 'Exception User',
            'username' => 'excuser_' . time() . rand(10, 99),
            'email' => 'excuser_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            },
        ]);

        $this->actingAs($customer, 'web');
        $response = $this->postJson(route('chatbot.chat'), [
            'question' => 'Help me with my order',
        ]);

        $response->assertStatus(500);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure(['support_message_id']);

        $supportMessageId = $response->json('support_message_id');
        $this->assertNotNull($supportMessageId);

        $contactMessage = ContactMessage::findOrFail($supportMessageId);
        $this->assertSame('AI Support Handoff — AI Failure', $contactMessage->subject);
        $this->assertStringContainsString('Help me with my order', $contactMessage->message);
        $this->assertStringContainsString('Connection timed out', $contactMessage->message);
    }
}
