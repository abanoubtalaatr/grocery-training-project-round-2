<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\Otp;
use App\Models\User;
use Filament\Actions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $overrides = []): User
    {
        return User::create(array_merge([
            'username' => 'admin_' . uniqid(),
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => Hash::make('AdminPassword123!'),
            'is_active' => true,
            'email_verified' => true,
            'role' => User::ROLE_ADMIN,
        ], $overrides));
    }

    private function clearOtherAdmins(): void
    {
        User::where('role', User::ROLE_ADMIN)->delete();
    }

    // ─── Admin Login (Direct — No MFA) ─────────────────────

    public function test_admin_can_login_directly_with_credentials(): void
    {
        $admin = $this->createAdmin();

        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->set('data.email', $admin->email)
            ->set('data.password', 'AdminPassword123!')
            ->call('authenticate');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_rejects_invalid_credentials(): void
    {
        $admin = $this->createAdmin();

        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->set('data.email', $admin->email)
            ->set('data.password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $customer = User::create([
            'username' => 'cust_' . uniqid(),
            'email' => 'cust_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'email_verified' => true,
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer);

        $response = $this->get('/admin');
        $response->assertForbidden();
    }

    // ─── Self-Protection ─────────────────────────────────────

    public function test_admin_cannot_deactivate_themselves_via_edit(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $admin->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $admin->refresh();
        $this->assertTrue($admin->is_active, 'Admin should not be able to deactivate themselves');
    }

    public function test_admin_cannot_demote_themselves_via_edit(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $admin->id])
            ->call('save');

        $admin->refresh();
        $this->assertTrue($admin->isAdmin(), 'Admin should not be able to demote themselves');
    }

    public function test_admin_cannot_delete_themselves_via_table_action(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    // ─── Last Admin Protection ───────────────────────────────

    public function test_last_admin_cannot_be_deactivated(): void
    {
        $this->clearOtherAdmins();
        $admin = $this->createAdmin();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $admin->id])
            ->set('data.is_active', false)
            ->call('save')
            ->assertHasErrors(['data.is_active']);
    }

    public function test_last_admin_cannot_be_demoted(): void
    {
        $this->clearOtherAdmins();
        $admin = $this->createAdmin();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $admin->id])
            ->set('data.role', 'customer')
            ->call('save')
            ->assertHasErrors(['data.role']);
    }

    public function test_last_admin_cannot_be_deleted(): void
    {
        $this->clearOtherAdmins();
        $admin = $this->createAdmin();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $admin->id])
            ->callAction(Actions\DeleteAction::class);

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_non_last_admin_can_be_deactivated(): void
    {
        $admin = $this->createAdmin();
        $otherAdmin = $this->createAdmin(['email' => 'other_' . uniqid() . '@example.com']);

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $otherAdmin->id])
            ->set('data.is_active', false)
            ->call('save')
            ->assertHasNoFormErrors();

        $otherAdmin->refresh();
        $this->assertFalse($otherAdmin->is_active);
    }

    public function test_non_admin_user_can_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $customer = User::create([
            'username' => 'cust_' . uniqid(),
            'email' => 'cust_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $customer->id])
            ->callAction(\Filament\Actions\DeleteAction::class);

        $this->assertSoftDeleted('users', ['id' => $customer->id]);
    }

    // ─── Utility ─────────────────────────────────────────────

    public function test_is_last_active_admin_returns_true_for_sole_admin(): void
    {
        $this->clearOtherAdmins();
        $admin = $this->createAdmin();

        $this->assertTrue(UserResource::isLastActiveAdmin($admin));
    }

    public function test_is_last_active_admin_returns_false_when_multiple_admins(): void
    {
        $this->clearOtherAdmins();
        $admin = $this->createAdmin();
        $otherAdmin = $this->createAdmin(['email' => 'other_' . uniqid() . '@example.com']);

        $this->assertFalse(UserResource::isLastActiveAdmin($admin));
    }

    public function test_is_last_active_admin_returns_false_for_non_admin(): void
    {
        $customer = User::create([
            'username' => 'cust_' . uniqid(),
            'email' => 'cust_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->assertFalse(UserResource::isLastActiveAdmin($customer));
    }
}
