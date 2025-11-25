<?php

namespace Database\Seeders;

use App\Models\CashBalance;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class CashBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users for relationships
        $admin = User::role('admin')->first();
        $cashier = User::role('cashier')->first();

        if (! $admin || ! $cashier) {
            $this->command->warn('⚠️  Users not found. Please run UserSeeder first.');

            return;
        }

        $this->command->info('🏦 Seeding Cash Balance data...');

        // 1. Create reconciled balance for 3 months ago
        $this->command->info('Creating historical balance periods...');

        $threeMonthsAgo = now()->subMonths(3);
        $balance1 = CashBalance::create([
            'opening_balance' => 10000000, // 10M IDR
            'closing_balance' => 12500000, // 12.5M IDR
            'period_start' => $threeMonthsAgo->copy()->startOfMonth(),
            'period_end' => $threeMonthsAgo->copy()->endOfMonth(),
            'reconciled_by' => $admin->id,
            'reconciliation_date' => $threeMonthsAgo->copy()->endOfMonth()->addDay(),
            'status' => 'reconciled',
        ]);
        $this->createTransactionsForPeriod($balance1, $cashier);

        // 2. Create reconciled balance for 2 months ago (with discrepancy)
        $twoMonthsAgo = now()->subMonths(2);
        $balance2 = CashBalance::create([
            'opening_balance' => 12500000, // From previous closing
            'closing_balance' => 11750000,
            'period_start' => $twoMonthsAgo->copy()->startOfMonth(),
            'period_end' => $twoMonthsAgo->copy()->endOfMonth(),
            'discrepancy_amount' => -50000, // 50K discrepancy
            'discrepancy_notes' => 'Small discrepancy found during reconciliation. Likely due to petty expenses not recorded.',
            'reconciled_by' => $admin->id,
            'reconciliation_date' => $twoMonthsAgo->copy()->endOfMonth()->addDay(),
            'status' => 'reconciled',
        ]);
        $this->createTransactionsForPeriod($balance2, $cashier);

        // 3. Create reconciled balance for 1 month ago
        $oneMonthAgo = now()->subMonths(1);
        $balance3 = CashBalance::create([
            'opening_balance' => 11750000, // From previous reconciled amount
            'closing_balance' => 15200000,
            'period_start' => $oneMonthAgo->copy()->startOfMonth(),
            'period_end' => $oneMonthAgo->copy()->endOfMonth(),
            'reconciled_by' => $cashier->id,
            'reconciliation_date' => $oneMonthAgo->copy()->endOfMonth()->addDay(),
            'status' => 'reconciled',
        ]);
        $this->createTransactionsForPeriod($balance3, $cashier);

        // 4. Create active balance for current month (not reconciled yet)
        $this->command->info('Creating current active balance period...');

        $currentMonth = CashBalance::create([
            'opening_balance' => 15200000, // From previous closing
            'closing_balance' => null,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'status' => 'active',
        ]);
        $this->createTransactionsForPeriod($currentMonth, $cashier, true);

        // 5. Create upcoming balance for next month
        $this->command->info('Creating upcoming balance period...');

        $nextMonth = now()->addMonth();
        CashBalance::create([
            'opening_balance' => 15000000, // Estimated
            'closing_balance' => null,
            'period_start' => $nextMonth->copy()->startOfMonth(),
            'period_end' => $nextMonth->copy()->endOfMonth(),
            'status' => 'active',
        ]);

        $this->command->info('✅ Cash Balance data seeded successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - 3 reconciled periods (historical)');
        $this->command->info('   - 1 active period (current month)');
        $this->command->info('   - 1 upcoming period (next month)');
    }

    /**
     * Create realistic transactions for a balance period.
     */
    private function createTransactionsForPeriod(CashBalance $balance, User $user, bool $isCurrentPeriod = false): void
    {
        $categories = Category::active()->get();

        if ($categories->isEmpty()) {
            $this->command->warn('⚠️  No categories found. Skipping transaction creation.');

            return;
        }

        // Determine how many days to generate transactions for
        $endDate = $isCurrentPeriod ? now() : $balance->period_end;
        $daysInPeriod = $balance->period_start->diffInDays($endDate);

        // Create cash in transactions (5-10 per period)
        $cashInCount = rand(5, 10);
        for ($i = 0; $i < $cashInCount; $i++) {
            $transactionDate = $balance->period_start->copy()->addDays(rand(0, $daysInPeriod));

            Transaction::create([
                'transaction_number' => 'TXN-'.$transactionDate->format('Y').'-'.str_pad(Transaction::count() + 1, 5, '0', STR_PAD_LEFT),
                'type' => 'in',
                'amount' => rand(500000, 3000000), // 500K - 3M IDR
                'description' => $this->getCashInDescription(),
                'transaction_date' => $transactionDate,
                'category_id' => $categories->random()->id,
                'user_id' => $user->id,
                'status' => 'approved',
                'approved_by' => User::role('admin')->first()->id,
                'approved_at' => $transactionDate->copy()->addHours(2),
            ]);
        }

        // Create cash out transactions (15-25 per period)
        $cashOutCount = rand(15, 25);
        for ($i = 0; $i < $cashOutCount; $i++) {
            $transactionDate = $balance->period_start->copy()->addDays(rand(0, $daysInPeriod));

            $status = 'approved';
            $approvedBy = User::role('admin')->first()->id;
            $approvedAt = $transactionDate->copy()->addHours(2);

            // Make some transactions pending if current period
            if ($isCurrentPeriod && rand(1, 5) === 1) {
                $status = 'pending';
                $approvedBy = null;
                $approvedAt = null;
            }

            Transaction::create([
                'transaction_number' => 'TXN-'.$transactionDate->format('Y').'-'.str_pad(Transaction::count() + 1, 5, '0', STR_PAD_LEFT),
                'type' => 'out',
                'amount' => rand(50000, 1500000), // 50K - 1.5M IDR
                'description' => $this->getCashOutDescription(),
                'transaction_date' => $transactionDate,
                'category_id' => $categories->random()->id,
                'user_id' => $user->id,
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
            ]);
        }

        $this->command->info("   ✓ Created transactions for period: {$balance->period_start->format('M Y')}");
    }

    /**
     * Get random cash in descriptions.
     */
    private function getCashInDescription(): string
    {
        $descriptions = [
            'Cash reimbursement from head office',
            'Revenue collection from branch',
            'Petty cash top-up from main account',
            'Cash deposit from finance department',
            'Reimbursement for approved expenses',
            'Cash transfer from operations',
            'Monthly petty cash allocation',
            'Cash replenishment - regular',
            'Budget transfer for operational expenses',
            'Cash collection from regional office',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Get random cash out descriptions.
     */
    private function getCashOutDescription(): string
    {
        $descriptions = [
            'Office supplies purchase',
            'Utility bills payment',
            'Staff meal allowance',
            'Transportation reimbursement',
            'Office cleaning services',
            'Printing and photocopy expenses',
            'Courier and delivery fees',
            'Office maintenance and repairs',
            'Pantry supplies restocking',
            'Staff training materials',
            'Internet and phone bills',
            'Office equipment minor repairs',
            'Employee travel reimbursement',
            'Meeting refreshments',
            'Stationery and supplies',
            'Parking and toll fees',
            'Emergency repairs',
            'Guest hospitality expenses',
            'Office decoration and plants',
            'Safety equipment purchase',
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
