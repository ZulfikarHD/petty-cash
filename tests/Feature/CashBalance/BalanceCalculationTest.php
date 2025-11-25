<?php

namespace Tests\Feature\CashBalance;

use App\Models\CashBalance;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        $this->balanceService = app(BalanceService::class);
    }

    public function test_current_balance_is_zero_with_no_data(): void
    {
        $balance = $this->balanceService->getCurrentBalance();

        $this->assertEquals(0.0, $balance);
    }

    public function test_current_balance_includes_opening_balance(): void
    {
        $user = User::factory()->create();

        CashBalance::factory()->currentMonth()->create([
            'opening_balance' => 10000.00,
            'created_by' => $user->id,
        ]);

        $balance = $this->balanceService->getCurrentBalance();

        $this->assertEquals(10000.0, $balance);
    }

    public function test_current_balance_adds_approved_cash_in_transactions(): void
    {
        $user = User::factory()->create();

        CashBalance::factory()->currentMonth()->create([
            'opening_balance' => 10000.00,
            'created_by' => $user->id,
        ]);

        Transaction::factory()->approved()->cashIn()->create([
            'user_id' => $user->id,
            'amount' => 5000.00,
            'transaction_date' => now(),
        ]);

        $balance = $this->balanceService->getCurrentBalance();

        $this->assertEquals(15000.0, $balance);
    }

    public function test_current_balance_subtracts_approved_cash_out_transactions(): void
    {
        $user = User::factory()->create();

        CashBalance::factory()->currentMonth()->create([
            'opening_balance' => 10000.00,
            'created_by' => $user->id,
        ]);

        Transaction::factory()->approved()->cashOut()->create([
            'user_id' => $user->id,
            'amount' => 3000.00,
            'transaction_date' => now(),
        ]);

        $balance = $this->balanceService->getCurrentBalance();

        $this->assertEquals(7000.0, $balance);
    }

    public function test_current_balance_ignores_pending_transactions(): void
    {
        $user = User::factory()->create();

        CashBalance::factory()->currentMonth()->create([
            'opening_balance' => 10000.00,
            'created_by' => $user->id,
        ]);

        // Pending transaction should not affect balance
        Transaction::factory()->cashIn()->create([
            'user_id' => $user->id,
            'amount' => 5000.00,
            'status' => 'pending',
            'transaction_date' => now(),
        ]);

        $balance = $this->balanceService->getCurrentBalance();

        $this->assertEquals(10000.0, $balance);
    }

    public function test_current_balance_ignores_rejected_transactions(): void
    {
        $user = User::factory()->create();

        CashBalance::factory()->currentMonth()->create([
            'opening_balance' => 10000.00,
            'created_by' => $user->id,
        ]);

        Transaction::factory()->rejected()->cashIn()->create([
            'user_id' => $user->id,
            'amount' => 5000.00,
            'transaction_date' => now(),
        ]);

        $balance = $this->balanceService->getCurrentBalance();

        $this->assertEquals(10000.0, $balance);
    }

    public function test_period_balance_calculation(): void
    {
        $user = User::factory()->create();

        $cashBalance = CashBalance::factory()->currentMonth()->create([
            'opening_balance' => 10000.00,
            'created_by' => $user->id,
        ]);

        Transaction::factory()->approved()->cashIn()->create([
            'user_id' => $user->id,
            'amount' => 5000.00,
            'transaction_date' => now(),
        ]);

        Transaction::factory()->approved()->cashOut()->create([
            'user_id' => $user->id,
            'amount' => 2000.00,
            'transaction_date' => now(),
        ]);

        $periodBalance = $this->balanceService->getPeriodBalance(
            $cashBalance->period_start,
            $cashBalance->period_end
        );

        $this->assertEquals(10000.0, $periodBalance['opening_balance']);
        $this->assertEquals(5000.0, $periodBalance['cash_in']);
        $this->assertEquals(2000.0, $periodBalance['cash_out']);
        $this->assertEquals(3000.0, $periodBalance['net_flow']);
        $this->assertEquals(13000.0, $periodBalance['closing_balance']);
    }

    public function test_low_balance_alert_triggers_when_below_threshold(): void
    {
        $this->assertTrue($this->balanceService->needsLowBalanceAlert(500.00));
        $this->assertFalse($this->balanceService->needsLowBalanceAlert(1500.00));
    }

    public function test_balance_history_returns_daily_snapshots(): void
    {
        $user = User::factory()->create();

        $startDate = now()->startOfMonth();
        $endDate = now()->startOfMonth()->addDays(2);

        CashBalance::factory()->create([
            'period_start' => $startDate,
            'period_end' => $endDate,
            'opening_balance' => 10000.00,
            'created_by' => $user->id,
        ]);

        Transaction::factory()->approved()->cashIn()->create([
            'user_id' => $user->id,
            'amount' => 1000.00,
            'transaction_date' => $startDate,
        ]);

        Transaction::factory()->approved()->cashOut()->create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'transaction_date' => $startDate->copy()->addDay(),
        ]);

        $history = $this->balanceService->getBalanceHistory($startDate, $endDate);

        $this->assertCount(3, $history); // 3 days

        // First day: opening + 1000 in
        $this->assertEquals(11000.0, $history[0]['balance']);

        // Second day: -500 out
        $this->assertEquals(10500.0, $history[1]['balance']);

        // Third day: no transactions
        $this->assertEquals(10500.0, $history[2]['balance']);
    }

    public function test_get_opening_balance_from_previous_period(): void
    {
        $user = User::factory()->create();

        // Create a previous month's reconciled balance
        CashBalance::factory()->previousMonth()->reconciled()->create([
            'opening_balance' => 8000.00,
            'closing_balance' => 12000.00,
            'created_by' => $user->id,
        ]);

        $openingBalance = $this->balanceService->getOpeningBalance(now());

        $this->assertEquals(12000.0, $openingBalance);
    }

    public function test_today_summary_calculation(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->approved()->cashIn()->create([
            'user_id' => $user->id,
            'amount' => 5000.00,
            'transaction_date' => today(),
        ]);

        Transaction::factory()->approved()->cashOut()->create([
            'user_id' => $user->id,
            'amount' => 2000.00,
            'transaction_date' => today(),
        ]);

        $summary = $this->balanceService->getTodaySummary();

        $this->assertEquals(5000.0, $summary['cash_in']);
        $this->assertEquals(2000.0, $summary['cash_out']);
        $this->assertEquals(3000.0, $summary['net_flow']);
    }

    public function test_has_overlapping_period_detection(): void
    {
        $user = User::factory()->create();

        CashBalance::factory()->create([
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'created_by' => $user->id,
        ]);

        // Overlapping period
        $this->assertTrue($this->balanceService->hasOverlappingPeriod(
            now()->startOfMonth(),
            now()->endOfMonth()
        ));

        // Non-overlapping period (next month)
        $this->assertFalse($this->balanceService->hasOverlappingPeriod(
            now()->addMonth()->startOfMonth(),
            now()->addMonth()->endOfMonth()
        ));
    }

    public function test_get_transactions_for_period(): void
    {
        $user = User::factory()->create();

        $cashBalance = CashBalance::factory()->currentMonth()->create([
            'created_by' => $user->id,
        ]);

        // Transaction within period
        $withinPeriod = Transaction::factory()->approved()->create([
            'user_id' => $user->id,
            'transaction_date' => now(),
        ]);

        // Transaction outside period
        Transaction::factory()->approved()->create([
            'user_id' => $user->id,
            'transaction_date' => now()->subMonths(2),
        ]);

        $transactions = $this->balanceService->getTransactionsForPeriod($cashBalance);

        $this->assertCount(1, $transactions);
        $this->assertEquals($withinPeriod->id, $transactions->first()->id);
    }
}
