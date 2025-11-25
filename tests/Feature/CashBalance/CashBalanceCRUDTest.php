<?php

namespace Tests\Feature\CashBalance;

use App\Models\CashBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashBalanceCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_cashier_can_view_cash_balance_index(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->get('/cash-balances');

        $response->assertOk();
    }

    public function test_admin_can_view_cash_balance_index(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get('/cash-balances');

        $response->assertOk();
    }

    public function test_cashier_can_create_cash_balance_period(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->post('/cash-balances', [
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'opening_balance' => 10000.00,
            'notes' => 'Opening balance for the month',
        ]);

        $response->assertRedirect('/cash-balances');
        $this->assertDatabaseHas('cash_balances', [
            'opening_balance' => 10000.00,
            'status' => 'active',
            'created_by' => $cashier->id,
        ]);
    }

    public function test_cash_balance_creation_requires_valid_data(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->post('/cash-balances', [
            'period_start' => '',
            'period_end' => '',
            'opening_balance' => -100,
        ]);

        $response->assertSessionHasErrors(['period_start', 'period_end', 'opening_balance']);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->post('/cash-balances', [
            'period_start' => '2024-12-31',
            'period_end' => '2024-12-01',
            'opening_balance' => 10000.00,
        ]);

        $response->assertSessionHasErrors(['period_end']);
    }

    public function test_cashier_can_view_cash_balance_details(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $cashBalance = CashBalance::factory()->create([
            'created_by' => $cashier->id,
        ]);

        $response = $this->actingAs($cashier)->get("/cash-balances/{$cashBalance->id}");

        $response->assertOk();
    }

    public function test_cashier_can_view_reconciliation_form(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $cashBalance = CashBalance::factory()->currentMonth()->create([
            'created_by' => $cashier->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($cashier)->get("/cash-balances/{$cashBalance->id}/reconcile");

        $response->assertOk();
    }

    public function test_cashier_can_reconcile_cash_balance(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $cashBalance = CashBalance::factory()->currentMonth()->create([
            'created_by' => $cashier->id,
            'status' => 'active',
            'opening_balance' => 10000.00,
        ]);

        $response = $this->actingAs($cashier)->post("/cash-balances/{$cashBalance->id}/reconciliation", [
            'actual_balance' => 10000.00,
            'has_discrepancy' => false,
        ]);

        $response->assertRedirect("/cash-balances/{$cashBalance->id}");

        $cashBalance->refresh();
        $this->assertEquals('reconciled', $cashBalance->status);
        $this->assertEquals($cashier->id, $cashBalance->reconciled_by);
    }

    public function test_reconciliation_with_discrepancy_requires_notes(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $cashBalance = CashBalance::factory()->currentMonth()->create([
            'created_by' => $cashier->id,
            'status' => 'active',
            'opening_balance' => 10000.00,
        ]);

        $response = $this->actingAs($cashier)->post("/cash-balances/{$cashBalance->id}/reconciliation", [
            'actual_balance' => 9500.00,
            'has_discrepancy' => true,
            'discrepancy_notes' => '',
        ]);

        $response->assertSessionHasErrors(['discrepancy_notes']);
    }

    public function test_reconciliation_with_discrepancy_and_notes_succeeds(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $cashBalance = CashBalance::factory()->currentMonth()->create([
            'created_by' => $cashier->id,
            'status' => 'active',
            'opening_balance' => 10000.00,
        ]);

        $response = $this->actingAs($cashier)->post("/cash-balances/{$cashBalance->id}/reconciliation", [
            'actual_balance' => 9500.00,
            'has_discrepancy' => true,
            'discrepancy_notes' => 'Missing receipt for coffee purchase',
        ]);

        $response->assertRedirect("/cash-balances/{$cashBalance->id}");

        $cashBalance->refresh();
        $this->assertEquals('reconciled', $cashBalance->status);
        $this->assertEquals('Missing receipt for coffee purchase', $cashBalance->discrepancy_notes);
    }

    public function test_cannot_reconcile_already_reconciled_balance(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $cashBalance = CashBalance::factory()->reconciled()->create([
            'created_by' => $cashier->id,
        ]);

        $response = $this->actingAs($cashier)->post("/cash-balances/{$cashBalance->id}/reconciliation", [
            'actual_balance' => 10000.00,
        ]);

        $response->assertSessionHasErrors(['actual_balance']);
    }

    public function test_cashier_can_delete_active_cash_balance(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $cashBalance = CashBalance::factory()->create([
            'created_by' => $cashier->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($cashier)->delete("/cash-balances/{$cashBalance->id}");

        $response->assertRedirect('/cash-balances');
        $this->assertSoftDeleted('cash_balances', [
            'id' => $cashBalance->id,
        ]);
    }

    public function test_cannot_delete_reconciled_cash_balance(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $cashBalance = CashBalance::factory()->reconciled()->create([
            'created_by' => $cashier->id,
        ]);

        $response = $this->actingAs($cashier)->delete("/cash-balances/{$cashBalance->id}");

        $response->assertRedirect('/cash-balances');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cash_balances', [
            'id' => $cashBalance->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cannot_create_overlapping_periods(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        // Create an existing period
        CashBalance::factory()->create([
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'created_by' => $cashier->id,
        ]);

        // Try to create an overlapping period
        $response = $this->actingAs($cashier)->post('/cash-balances', [
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'opening_balance' => 5000.00,
        ]);

        $response->assertSessionHasErrors(['period_start']);
    }

    public function test_cash_balance_index_shows_current_balance(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->get('/cash-balances');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('currentBalance')
            ->has('lowBalanceAlert')
            ->has('lowBalanceThreshold')
        );
    }

    public function test_cash_balance_index_can_filter_by_status(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        CashBalance::factory()->create(['status' => 'active', 'created_by' => $cashier->id]);
        CashBalance::factory()->reconciled()->create(['created_by' => $cashier->id]);

        $response = $this->actingAs($cashier)->get('/cash-balances?status=active');

        $response->assertOk();
    }
}
