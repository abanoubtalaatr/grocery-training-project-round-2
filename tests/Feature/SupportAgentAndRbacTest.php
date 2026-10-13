<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SupportAgentAndRbacTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test role distinctions and EnsureUserHasRole middleware.
     */
    public function test_user_roles_and_rbac_middleware(): void
    {
        $customer = User::create([
            'name' => 'Customer User',
            'username' => 'cust_' . time() . rand(10, 99),
            'email' => 'customer_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $supportAgent = User::create([
            'name' => 'Support Agent User',
            'username' => 'agent_' . time() . rand(10, 99),
            'email' => 'agent_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_SUPPORT_AGENT,
        ]);

        $deliveryPartner = User::create([
            'name' => 'Driver User',
            'username' => 'driver_' . time() . rand(10, 99),
            'email' => 'driver_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_DELIVERY_PARTNER,
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin_' . time() . rand(10, 99),
            'email' => 'admin_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_ADMIN,
        ]);

        // Customer role checks
        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isSupportAgent());
        $this->assertFalse($customer->isDeliveryPartner());
        $this->assertFalse($customer->isAdmin());

        // Support Agent role checks
        $this->assertTrue($supportAgent->isSupportAgent());
        $this->assertFalse($supportAgent->isCustomer());
        $this->assertFalse($supportAgent->isDeliveryPartner());

        // Delivery Partner role checks
        $this->assertTrue($deliveryPartner->isDeliveryPartner());
        $this->assertFalse($deliveryPartner->isCustomer());

        // Admin role checks
        $this->assertTrue($admin->isAdmin());

        // Access control: Customer accessing support agent messages -> 403 Forbidden
        $this->actingAs($customer, 'sanctum');
        $customerResponse = $this->getJson('/api/support/messages');
        $customerResponse->assertStatus(403);

        // Access control: Delivery partner accessing support agent messages -> 403 Forbidden
        $this->actingAs($deliveryPartner, 'sanctum');
        $driverResponse = $this->getJson('/api/support/messages');
        $driverResponse->assertStatus(403);

        // Access control: Support agent accessing support messages -> 200 OK
        $this->actingAs($supportAgent, 'sanctum');
        $agentResponse = $this->getJson('/api/support/messages');
        $agentResponse->assertStatus(200);

        // Access control: Admin accessing support messages -> 200 OK
        $this->actingAs($admin, 'sanctum');
        $adminResponse = $this->getJson('/api/support/messages');
        $adminResponse->assertStatus(200);
    }

    /**
     * Test Support Agent message triage, reply flow, and customer message history.
     */
    public function test_support_agent_can_view_and_reply_to_messages(): void
    {
        $customer = User::create([
            'name' => 'Inquiring Customer',
            'username' => 'inq_' . time() . rand(10, 99),
            'email' => 'inquiry_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $supportAgent = User::create([
            'name' => 'Active Agent',
            'username' => 'active_' . time() . rand(10, 99),
            'email' => 'active_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_SUPPORT_AGENT,
        ]);

        // Customer submits a contact/support message
        $this->actingAs($customer, 'sanctum');
        $submitResponse = $this->postJson('/api/contact', [
            'name' => 'Inquiring Customer',
            'email' => $customer->email,
            'subject' => 'Need help with order #1234',
            'message' => 'Hello, I have an inquiry regarding my scheduled grocery delivery.',
        ]);

        $submitResponse->assertStatus(201);
        $messageId = $submitResponse->json('data.id');
        $this->assertNotNull($messageId);

        // Verify message is linked to customer
        $message = ContactMessage::findOrFail($messageId);
        $this->assertSame($customer->id, $message->user_id);
        $this->assertSame('new', $message->status);

        // Support Agent views message
        $this->actingAs($supportAgent, 'sanctum');
        $showResponse = $this->getJson("/api/support/messages/{$messageId}");
        $showResponse->assertStatus(200);

        // Status is automatically marked as 'read' upon agent view
        $message->refresh();
        $this->assertSame('read', $message->status);

        // Support Agent submits a reply
        $replyResponse = $this->postJson("/api/support/messages/{$messageId}/reply", [
            'reply' => 'Hello, your order has been prepared and will arrive on time.',
            'admin_notes' => 'Customer was reassured about delivery time.',
        ]);

        $replyResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'replied')
            ->assertJsonPath('data.reply', 'Hello, your order has been prepared and will arrive on time.');

        $message->refresh();
        $this->assertSame('replied', $message->status);
        $this->assertSame($supportAgent->id, $message->replied_by);
        $this->assertNotNull($message->replied_at);

        // Customer views their support message history
        $this->actingAs($customer, 'sanctum');
        $customerHistoryResponse = $this->getJson('/api/support/my-messages');
        $customerHistoryResponse->assertStatus(200);

        $customerItems = $customerHistoryResponse->json('data');
        $this->assertNotEmpty($customerItems);
        $foundMessage = collect($customerItems)->firstWhere('id', $messageId);
        $this->assertNotNull($foundMessage);
        $this->assertSame('replied', $foundMessage['status']);
        $this->assertSame('Hello, your order has been prepared and will arrive on time.', $foundMessage['reply']);
    }
}
