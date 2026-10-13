<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\StaticPage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebSupportTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        ContactMessage::query()->delete();

        $this->user = User::create([
            'username' => 'supuser_' . Str::random(6),
            'email' => 'sup_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Sup',
            'lastname' => 'User',
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    public function test_guest_can_view_support_index(): void
    {
        $response = $this->get(route('support.index'));

        $response->assertStatus(200);
        $response->assertSee(trans('support.heading'));
    }

    public function test_user_can_view_support_index(): void
    {
        $this->login();

        $response = $this->get(route('support.index'));

        $response->assertStatus(200);
        $response->assertSee(trans('support.heading'));
    }

    public function test_user_can_view_faqs_page(): void
    {
        Faq::create([
            'question' => 'What is the return policy?',
            'answer' => 'You can return within 30 days.',
            'category' => 'General',
            'order' => 1,
            'is_active' => true,
        ]);

        $this->login();

        $response = $this->get(route('support.faqs'));

        $response->assertStatus(200);
        $response->assertSee('What is the return policy?');
        $response->assertSee(trans('support.faqs.heading'));
    }

    public function test_faqs_can_be_filtered_by_category(): void
    {
        Faq::create([
            'question' => 'General question?',
            'answer' => 'General answer.',
            'category' => 'General',
            'order' => 1,
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Technical question?',
            'answer' => 'Technical answer.',
            'category' => 'Technical',
            'order' => 2,
            'is_active' => true,
        ]);

        $this->login();

        $response = $this->get(route('support.faqs', ['category' => 'Technical']));

        $response->assertStatus(200);
        $response->assertSee('Technical question?');
        $response->assertDontSee('General question?');
    }

    public function test_faqs_can_be_searched(): void
    {
        Faq::create([
            'question' => 'How do I reset my password?',
            'answer' => 'Go to settings.',
            'category' => 'Account',
            'order' => 1,
            'is_active' => true,
        ]);

        $this->login();

        $response = $this->get(route('support.faqs', ['search' => 'password']));

        $response->assertStatus(200);
        $response->assertSee('How do I reset my password?');
    }

    public function test_user_can_view_policies_page(): void
    {
        $this->login();

        $response = $this->get(route('support.policies'));

        $response->assertStatus(200);
        $response->assertSee(trans('support.policies.heading'));
    }

    public function test_user_can_view_contact_form(): void
    {
        $this->login();

        $response = $this->get(route('support.contact'));

        $response->assertStatus(200);
        $response->assertSee(trans('support.contact.heading'));
        $response->assertSee(trans('support.contact.submit'));
    }

    public function test_user_can_submit_contact_form(): void
    {
        $this->login();

        $response = $this->post(route('support.contact.submit'), [
            'name' => 'Test User',
            'email' => $this->user->email,
            'subject' => 'Need help with order',
            'message' => 'I have a question about my recent order.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => $this->user->id,
            'subject' => 'Need help with order',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->login();

        $response = $this->post(route('support.contact.submit'), [
            'name' => '',
            'email' => '',
            'subject' => '',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_user_can_view_their_messages(): void
    {
        ContactMessage::create([
            'user_id' => $this->user->id,
            'name' => 'Test User',
            'email' => $this->user->email,
            'subject' => 'Help needed',
            'message' => 'I need help.',
            'status' => 'new',
        ]);

        $this->login();

        $response = $this->get(route('support.messages'));

        $response->assertStatus(200);
        $response->assertSee('Help needed');
        $response->assertSee(trans('support.messages.heading'));
    }

    public function test_user_can_view_single_message(): void
    {
        $msg = ContactMessage::create([
            'user_id' => $this->user->id,
            'name' => 'Test User',
            'email' => $this->user->email,
            'subject' => 'Specific issue',
            'message' => 'Detailed description of my issue.',
            'status' => 'replied',
            'reply' => 'We have resolved your issue.',
            'replied_at' => now(),
        ]);

        $this->login();

        $response = $this->get(route('support.messages.show', $msg->id));

        $response->assertStatus(200);
        $response->assertSee('Specific issue');
        $response->assertSee('Detailed description of my issue.');
        $response->assertSee('We have resolved your issue.');
        $response->assertSee(trans('support.messages.reply_from_support'));
    }

    public function test_user_cannot_view_other_users_message(): void
    {
        $otherUser = User::create([
            'username' => 'othersup_' . Str::random(6),
            'email' => 'othersup_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'Sup',
        ]);

        $msg = ContactMessage::create([
            'user_id' => $otherUser->id,
            'name' => 'Other User',
            'email' => $otherUser->email,
            'subject' => 'Private message',
            'message' => 'Private content.',
            'status' => 'new',
        ]);

        $this->login();

        $response = $this->get(route('support.messages.show', $msg->id));

        $response->assertStatus(404);
    }

    public function test_messages_page_shows_empty_state(): void
    {
        $this->login();

        $response = $this->get(route('support.messages'));

        $response->assertStatus(200);
        $response->assertSee(trans('support.messages.no_messages'));
    }

    public function test_guest_can_view_faqs(): void
    {
        $response = $this->get(route('support.faqs'));

        $response->assertStatus(200);
        $response->assertSee(trans('support.faqs.heading'));
    }

    public function test_guest_can_view_policies(): void
    {
        $response = $this->get(route('support.policies'));

        $response->assertStatus(200);
        $response->assertSee(trans('support.policies.heading'));
    }

    public function test_guest_can_view_contact_form(): void
    {
        $response = $this->get(route('support.contact'));

        $response->assertStatus(200);
        $response->assertSee(trans('support.contact.heading'));
    }

    public function test_guest_can_submit_contact_form(): void
    {
        $response = $this->post(route('support.contact.submit'), [
            'name' => 'Guest User',
            'email' => 'guest_' . Str::random(6) . '@example.com',
            'subject' => 'Need help',
            'message' => 'I have a question.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_auth_only_messages_route_requires_login(): void
    {
        $response = $this->get(route('support.messages'));

        $response->assertRedirect();
    }

    public function test_about_us_link_resolves_to_correct_route(): void
    {
        $aboutUsUrl = route('support.index');

        $this->assertEquals(url('/help'), $aboutUsUrl);
    }

    public function test_footer_links_use_correct_named_routes(): void
    {
        $this->assertEquals(url('/help'), route('support.index'));
        $this->assertEquals(url('/help/faqs'), route('support.faqs'));
        $this->assertEquals(url('/help/policies'), route('support.policies'));
        $this->assertEquals(url('/help/contact'), route('support.contact'));
    }
}
