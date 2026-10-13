<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\ContactMessage;
use App\Models\SmartList;
use App\Models\User;
use App\Policies\ContactMessagePolicy;
use App\Policies\SmartListPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleBasedAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'username' => 'user_' . uniqid(),
            'email' => 'user_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'email_verified' => true,
            'role' => User::ROLE_CUSTOMER,
        ], $overrides));
    }

    // isAdmin() - role is single source of truth

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $admin = $this->createUser(['role' => User::ROLE_ADMIN]);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_is_admin_returns_false_for_customer_role(): void
    {
        $customer = $this->createUser(['role' => User::ROLE_CUSTOMER]);
        $this->assertFalse($customer->isAdmin());
    }

    public function test_is_admin_returns_false_for_delivery_partner_role(): void
    {
        $driver = $this->createUser(['role' => User::ROLE_DELIVERY_PARTNER]);
        $this->assertFalse($driver->isAdmin());
    }

    public function test_is_admin_returns_false_for_support_agent_role(): void
    {
        $agent = $this->createUser(['role' => User::ROLE_SUPPORT_AGENT]);
        $this->assertFalse($agent->isAdmin());
    }

    public function test_is_admin_ignores_is_admin_column(): void
    {
        $user = $this->createUser([
            'role' => User::ROLE_CUSTOMER,
            'is_admin' => true,
        ]);
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_admin_requires_admin_role_even_with_is_admin_false(): void
    {
        $user = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'is_admin' => false,
        ]);
        $this->assertTrue($user->isAdmin());
    }

    // scopeAdmin() - role-based query

    public function test_scope_admin_returns_only_admin_role_users(): void
    {
        $admin = $this->createUser(['role' => User::ROLE_ADMIN]);
        $customer = $this->createUser(['role' => User::ROLE_CUSTOMER]);

        $admins = User::admin()->get();

        $this->assertTrue($admins->contains('id', $admin->id));
        $this->assertFalse($admins->contains('id', $customer->id));
    }

    public function test_scope_admin_excludes_is_admin_true_without_admin_role(): void
    {
        $legacyUser = $this->createUser([
            'role' => User::ROLE_CUSTOMER,
            'is_admin' => true,
        ]);

        $admins = User::admin()->get();

        $this->assertFalse($admins->contains('id', $legacyUser->id));
    }

    // hasRole() - uses isAdmin() internally

    public function test_has_role_admin_for_admin_role_user(): void
    {
        $admin = $this->createUser(['role' => User::ROLE_ADMIN]);
        $this->assertTrue($admin->hasRole('admin'));
    }

    public function test_has_role_admin_false_for_customer(): void
    {
        $customer = $this->createUser(['role' => User::ROLE_CUSTOMER]);
        $this->assertFalse($customer->hasRole('admin'));
    }

    // isLastActiveAdmin() - role-based

    public function test_is_last_active_admin_true_for_sole_admin(): void
    {
        User::where('role', User::ROLE_ADMIN)->delete();
        $admin = $this->createUser(['role' => User::ROLE_ADMIN]);
        $this->assertTrue(UserResource::isLastActiveAdmin($admin));
    }

    public function test_is_last_active_admin_false_for_customer(): void
    {
        $customer = $this->createUser(['role' => User::ROLE_CUSTOMER]);
        $this->assertFalse(UserResource::isLastActiveAdmin($customer));
    }

    public function test_is_last_active_admin_false_with_multiple_admins(): void
    {
        $admin1 = $this->createUser(['role' => User::ROLE_ADMIN]);
        $admin2 = $this->createUser(['role' => User::ROLE_ADMIN]);
        $this->assertFalse(UserResource::isLastActiveAdmin($admin1));
    }

    public function test_is_last_active_admin_ignores_is_admin_column(): void
    {
        User::where('role', User::ROLE_ADMIN)->delete();
        $admin = $this->createUser(['role' => User::ROLE_ADMIN]);
        $legacyUser = $this->createUser([
            'role' => User::ROLE_CUSTOMER,
            'is_admin' => true,
        ]);

        $this->assertTrue(UserResource::isLastActiveAdmin($admin));
    }

    // ContactMessagePolicy - role-based

    public function test_contact_message_policy_allows_admin(): void
    {
        $admin = $this->createUser(['role' => User::ROLE_ADMIN]);
        $policy = new ContactMessagePolicy();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->update($admin, new ContactMessage()));
        $this->assertTrue($policy->delete($admin, new ContactMessage()));
    }

    public function test_contact_message_policy_allows_support_agent(): void
    {
        $agent = $this->createUser(['role' => User::ROLE_SUPPORT_AGENT]);
        $policy = new ContactMessagePolicy();

        $this->assertTrue($policy->viewAny($agent));
    }

    public function test_contact_message_policy_denies_customer(): void
    {
        $customer = $this->createUser(['role' => User::ROLE_CUSTOMER]);
        $policy = new ContactMessagePolicy();

        $this->assertFalse($policy->viewAny($customer));
        $this->assertFalse($policy->update($customer, new ContactMessage()));
        $this->assertFalse($policy->delete($customer, new ContactMessage()));
    }

    // SmartListPolicy - ownership-based

    public function test_smart_list_policy_allows_owner(): void
    {
        $user = $this->createUser();
        $smartList = SmartList::create(['user_id' => $user->id, 'name' => 'Test List']);
        $policy = new SmartListPolicy();

        $this->assertTrue($policy->view($user, $smartList));
        $this->assertTrue($policy->update($user, $smartList));
        $this->assertTrue($policy->delete($user, $smartList));
    }

    public function test_smart_list_policy_denies_non_owner(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $smartList = SmartList::create(['user_id' => $user->id, 'name' => 'Test List']);
        $policy = new SmartListPolicy();

        $this->assertFalse($policy->view($other, $smartList));
        $this->assertFalse($policy->update($other, $smartList));
        $this->assertFalse($policy->delete($other, $smartList));
    }
}
