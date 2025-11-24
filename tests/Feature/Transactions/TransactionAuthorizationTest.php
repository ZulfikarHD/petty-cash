<?php

namespace Tests\Feature\Transactions;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_user_without_permission_cannot_view_transactions(): void
    {
        $user = User::factory()->create();
        // User with no role/permission

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(403);
    }

    public function test_user_without_permission_cannot_create_transaction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/transactions', [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Test',
            'transaction_date' => today()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    }

    public function test_cashier_can_only_edit_own_pending_transactions(): void
    {
        $cashier1 = User::factory()->create();
        $cashier1->assignRole('Cashier');

        $cashier2 = User::factory()->create();
        $cashier2->assignRole('Cashier');

        $transaction = Transaction::factory()->create([
            'user_id' => $cashier2->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($cashier1)->put("/transactions/{$transaction->id}", [
            'type' => $transaction->type,
            'amount' => 150000,
            'description' => 'Updated',
            'transaction_date' => $transaction->transaction_date,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_edit_any_pending_transaction(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $transaction = Transaction::factory()->create([
            'user_id' => $cashier->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put("/transactions/{$transaction->id}", [
            'type' => $transaction->type,
            'amount' => 150000,
            'description' => 'Updated by admin',
            'transaction_date' => $transaction->transaction_date,
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 150000,
        ]);
    }

    public function test_cashier_can_only_delete_own_pending_transactions(): void
    {
        $cashier1 = User::factory()->create();
        $cashier1->assignRole('Cashier');

        $cashier2 = User::factory()->create();
        $cashier2->assignRole('Cashier');

        $transaction = Transaction::factory()->create([
            'user_id' => $cashier2->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($cashier1)->delete("/transactions/{$transaction->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_delete_any_pending_transaction(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $transaction = Transaction::factory()->create([
            'user_id' => $cashier->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->delete("/transactions/{$transaction->id}");

        $response->assertRedirect('/transactions');
        $this->assertSoftDeleted('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_requester_can_create_transactions(): void
    {
        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $response = $this->actingAs($requester)->post('/transactions', [
            'type' => 'out',
            'amount' => 25000,
            'description' => 'Request for supplies',
            'transaction_date' => today()->format('Y-m-d'),
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'description' => 'Request for supplies',
            'user_id' => $requester->id,
        ]);
    }

    public function test_accountant_can_view_all_transactions(): void
    {
        $this->withoutVite();

        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $response = $this->actingAs($accountant)->get('/transactions');

        $response->assertOk();
    }

    public function test_accountant_can_create_transactions(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $response = $this->actingAs($accountant)->post('/transactions', [
            'type' => 'in',
            'amount' => 500000,
            'description' => 'Bank deposit',
            'transaction_date' => today()->format('Y-m-d'),
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'description' => 'Bank deposit',
            'user_id' => $accountant->id,
        ]);
    }

    public function test_guest_cannot_access_transactions(): void
    {
        $response = $this->get('/transactions');

        $response->assertRedirect('/login');
    }
}
