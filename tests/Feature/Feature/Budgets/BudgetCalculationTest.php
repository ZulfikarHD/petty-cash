<?php

namespace Tests\Feature\Feature\Budgets;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_calculates_spent_amount_correctly(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $budget = Budget::factory()->create([
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        // Create approved cash-out transactions within budget period
        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 3000000,
            'status' => 'approved',
            'transaction_date' => '2025-01-15',
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 2000000,
            'status' => 'approved',
            'transaction_date' => '2025-01-20',
            'user_id' => $user->id,
        ]);

        $this->assertEquals(5000000, $budget->spent_amount);
        $this->assertEquals(5000000, $budget->remaining_amount);
        $this->assertEquals(50.0, $budget->percentage_spent);
    }

    public function test_only_counts_approved_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $budget = Budget::factory()->create([
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 3000000,
            'status' => 'approved',
            'transaction_date' => '2025-01-15',
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 2000000,
            'status' => 'pending',
            'transaction_date' => '2025-01-20',
            'user_id' => $user->id,
        ]);

        $this->assertEquals(3000000, $budget->spent_amount);
    }

    public function test_only_counts_cash_out_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $budget = Budget::factory()->create([
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 3000000,
            'status' => 'approved',
            'transaction_date' => '2025-01-15',
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'in',
            'amount' => 2000000,
            'status' => 'approved',
            'transaction_date' => '2025-01-20',
            'user_id' => $user->id,
        ]);

        $this->assertEquals(3000000, $budget->spent_amount);
    }

    public function test_only_counts_transactions_within_budget_period(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $budget = Budget::factory()->create([
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        // Within period
        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 3000000,
            'status' => 'approved',
            'transaction_date' => '2025-01-15',
            'user_id' => $user->id,
        ]);

        // Before period
        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 1000000,
            'status' => 'approved',
            'transaction_date' => '2024-12-31',
            'user_id' => $user->id,
        ]);

        // After period
        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 1000000,
            'status' => 'approved',
            'transaction_date' => '2025-02-01',
            'user_id' => $user->id,
        ]);

        $this->assertEquals(3000000, $budget->spent_amount);
    }

    public function test_detects_exceeded_budget(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $budget = Budget::factory()->create([
            'category_id' => $category->id,
            'amount' => 5000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 6000000,
            'status' => 'approved',
            'transaction_date' => '2025-01-15',
            'user_id' => $user->id,
        ]);

        $this->assertTrue($budget->isExceeded());
        $this->assertEquals(-1000000, $budget->remaining_amount);
        $this->assertEquals(120.0, $budget->percentage_spent);
    }

    public function test_detects_alert_threshold_reached(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $budget = Budget::factory()->create([
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'alert_threshold' => 80,
        ]);

        Transaction::factory()->create([
            'category_id' => $category->id,
            'type' => 'out',
            'amount' => 8500000,
            'status' => 'approved',
            'transaction_date' => '2025-01-15',
            'user_id' => $user->id,
        ]);

        $this->assertTrue($budget->isAlertThresholdReached());
        $this->assertFalse($budget->isExceeded());
    }

    public function test_budget_service_gets_active_budgets(): void
    {
        $service = app(BudgetService::class);

        // Active budget
        $activeCategory = Category::factory()->create();
        Budget::factory()->create([
            'category_id' => $activeCategory->id,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(5),
        ]);

        // Expired budget
        $expiredCategory = Category::factory()->create();
        Budget::factory()->create([
            'category_id' => $expiredCategory->id,
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(1),
        ]);

        $activeBudgets = $service->getActiveBudgets();

        $this->assertCount(1, $activeBudgets);
    }

    public function test_budget_service_gets_budget_alerts(): void
    {
        $user = User::factory()->create();
        $service = app(BudgetService::class);

        // Budget exceeding limit
        $category1 = Category::factory()->create();
        $budget1 = Budget::factory()->create([
            'category_id' => $category1->id,
            'amount' => 5000000,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(5),
        ]);
        Transaction::factory()->create([
            'category_id' => $category1->id,
            'type' => 'out',
            'amount' => 6000000,
            'status' => 'approved',
            'transaction_date' => now(),
            'user_id' => $user->id,
        ]);

        // Budget reaching alert threshold
        $category2 = Category::factory()->create();
        $budget2 = Budget::factory()->create([
            'category_id' => $category2->id,
            'amount' => 10000000,
            'alert_threshold' => 80,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(5),
        ]);
        Transaction::factory()->create([
            'category_id' => $category2->id,
            'type' => 'out',
            'amount' => 8500000,
            'status' => 'approved',
            'transaction_date' => now(),
            'user_id' => $user->id,
        ]);

        // Budget under threshold
        $category3 = Category::factory()->create();
        $budget3 = Budget::factory()->create([
            'category_id' => $category3->id,
            'amount' => 10000000,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(5),
        ]);
        Transaction::factory()->create([
            'category_id' => $category3->id,
            'type' => 'out',
            'amount' => 3000000,
            'status' => 'approved',
            'transaction_date' => now(),
            'user_id' => $user->id,
        ]);

        $alerts = $service->getBudgetAlerts();

        $this->assertCount(2, $alerts);
    }
}
