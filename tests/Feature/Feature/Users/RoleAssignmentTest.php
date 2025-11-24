<?php

namespace Tests\Feature\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_admin_can_assign_role_to_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['Cashier'],
        ]);

        $response->assertRedirect('/users');

        $user->refresh();
        $this->assertTrue($user->hasRole('Cashier'));
    }

    public function test_admin_can_assign_multiple_roles_to_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['Cashier', 'Accountant'],
        ]);

        $response->assertRedirect('/users');

        $user->refresh();
        $this->assertTrue($user->hasRole('Cashier'));
        $this->assertTrue($user->hasRole('Accountant'));
    }

    public function test_admin_can_remove_role_from_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $user = User::factory()->create();
        $user->assignRole('Cashier');

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [],
        ]);

        $response->assertRedirect('/users');

        $user->refresh();
        $this->assertFalse($user->hasRole('Cashier'));
    }

    public function test_user_with_role_has_correct_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Accountant');

        $this->assertTrue($user->can('view-transactions'));
        $this->assertTrue($user->can('approve-transactions'));
        $this->assertFalse($user->can('delete-users'));
    }

    public function test_admin_has_all_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->assertTrue($admin->can('manage-users'));
        $this->assertTrue($admin->can('manage-transactions'));
        $this->assertTrue($admin->can('manage-settings'));
        $this->assertTrue($admin->can('view-reports'));
    }

    public function test_requester_has_limited_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Requester');

        $this->assertTrue($user->can('view-transactions'));
        $this->assertTrue($user->can('create-transactions'));
        $this->assertFalse($user->can('approve-transactions'));
        $this->assertFalse($user->can('manage-users'));
    }

    public function test_cashier_can_manage_transactions_but_not_approve(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cashier');

        $this->assertTrue($user->can('view-transactions'));
        $this->assertTrue($user->can('create-transactions'));
        $this->assertTrue($user->can('manage-transactions'));
        $this->assertFalse($user->can('approve-transactions'));
    }

    public function test_non_admin_cannot_assign_roles(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $user = User::factory()->create();

        $response = $this->actingAs($cashier)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['Admin'],
        ]);

        $response->assertStatus(403);
    }
}
