<?php

namespace Tests\Feature\CashBalance;

use App\Models\CashBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashBalanceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_guests_cannot_access_cash_balance_pages(): void
    {
        $response = $this->get('/cash-balances');
        $response->assertRedirect('/login');

        $response = $this->get('/cash-balances/create');
        $response->assertRedirect('/login');

        $response = $this->post('/cash-balances');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_all_cash_balance_features(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Index
        $response = $this->actingAs($admin)->get('/cash-balances');
        $response->assertOk();

        // Create form
        $response = $this->actingAs($admin)->get('/cash-balances/create');
        $response->assertOk();

        // Store
        $response = $this->actingAs($admin)->post('/cash-balances', [
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'opening_balance' => 10000.00,
        ]);
        $response->assertRedirect('/cash-balances');

        // Show
        $cashBalance = CashBalance::first();
        $response = $this->actingAs($admin)->get("/cash-balances/{$cashBalance->id}");
        $response->assertOk();

        // Reconcile form
        $response = $this->actingAs($admin)->get("/cash-balances/{$cashBalance->id}/reconcile");
        $response->assertOk();
    }

    public function test_accountant_can_view_and_reconcile_cash_balances(): void
    {
        $this->withoutVite();

        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        // Index
        $response = $this->actingAs($accountant)->get('/cash-balances');
        $response->assertOk();

        // Create form
        $response = $this->actingAs($accountant)->get('/cash-balances/create');
        $response->assertOk();

        // Create a cash balance
        $cashBalance = CashBalance::factory()->currentMonth()->create([
            'created_by' => $accountant->id,
        ]);

        // Show
        $response = $this->actingAs($accountant)->get("/cash-balances/{$cashBalance->id}");
        $response->assertOk();

        // Reconcile form
        $response = $this->actingAs($accountant)->get("/cash-balances/{$cashBalance->id}/reconcile");
        $response->assertOk();
    }

    public function test_cashier_can_manage_cash_balances(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        // Index
        $response = $this->actingAs($cashier)->get('/cash-balances');
        $response->assertOk();

        // Create form
        $response = $this->actingAs($cashier)->get('/cash-balances/create');
        $response->assertOk();

        // Store
        $response = $this->actingAs($cashier)->post('/cash-balances', [
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'opening_balance' => 10000.00,
        ]);
        $response->assertRedirect('/cash-balances');

        // Show
        $cashBalance = CashBalance::first();
        $response = $this->actingAs($cashier)->get("/cash-balances/{$cashBalance->id}");
        $response->assertOk();
    }

    public function test_requester_can_view_cash_balance_but_not_manage(): void
    {
        $this->withoutVite();

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        // Index - should be able to view
        $response = $this->actingAs($requester)->get('/cash-balances');
        $response->assertOk();

        // Create form - should be forbidden
        $response = $this->actingAs($requester)->get('/cash-balances/create');
        $response->assertStatus(403);

        // Store - should be forbidden
        $response = $this->actingAs($requester)->post('/cash-balances', [
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'opening_balance' => 10000.00,
        ]);
        $response->assertStatus(403);
    }

    public function test_requester_cannot_reconcile_cash_balances(): void
    {
        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $cashBalance = CashBalance::factory()->currentMonth()->create();

        // Reconcile form - should be forbidden
        $response = $this->actingAs($requester)->get("/cash-balances/{$cashBalance->id}/reconcile");
        $response->assertStatus(403);

        // Store reconciliation - should be forbidden
        $response = $this->actingAs($requester)->post("/cash-balances/{$cashBalance->id}/reconciliation", [
            'actual_balance' => 10000.00,
        ]);
        $response->assertStatus(403);
    }

    public function test_requester_cannot_delete_cash_balances(): void
    {
        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $cashBalance = CashBalance::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($requester)->delete("/cash-balances/{$cashBalance->id}");
        $response->assertStatus(403);
    }

    public function test_user_without_role_cannot_access_cash_balances(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/cash-balances');
        $response->assertStatus(403);
    }
}
