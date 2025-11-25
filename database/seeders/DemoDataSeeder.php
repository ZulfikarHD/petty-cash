<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\CashBalance;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates a complete demo dataset with realistic data
     * for categories, budgets, transactions, and cash balances.
     */
    public function run(): void
    {
        $this->command->info('🎬 Starting Demo Data Seeding...');
        $this->command->newLine();

        // 1. Seed Categories
        $this->command->info('1️⃣  Creating Categories...');
        $categories = $this->seedCategories();
        $this->command->info("   ✅ Created {$categories->count()} categories");
        $this->command->newLine();

        // 2. Seed Budgets
        $this->command->info('2️⃣  Creating Budgets...');
        $budgets = $this->seedBudgets($categories);
        $this->command->info("   ✅ Created {$budgets->count()} budgets");
        $this->command->newLine();

        // 3. Seed Cash Balance Periods
        $this->command->info('3️⃣  Creating Cash Balance Periods...');
        $balancePeriods = $this->seedCashBalances();
        $this->command->info("   ✅ Created {$balancePeriods->count()} balance periods");
        $this->command->newLine();

        // 4. Seed Transactions
        $this->command->info('4️⃣  Creating Transactions...');
        $transactions = $this->seedTransactions($categories, $balancePeriods);
        $this->command->info("   ✅ Created {$transactions->count()} transactions");
        $this->command->newLine();

        // Summary
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✨ Demo Data Seeding Completed!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->table(
            ['Resource', 'Count'],
            [
                ['Categories', $categories->count()],
                ['Budgets', $budgets->count()],
                ['Balance Periods', $balancePeriods->count()],
                ['Transactions', $transactions->count()],
            ]
        );
        $this->command->newLine();
        $this->command->info('🎉 Your app is now loaded with demo data!');
        $this->command->info('🔐 Login credentials:');
        $this->command->info('   Admin: admin@pettycash.com / password');
        $this->command->info('   Cashier: cashier@pettycash.com / password');
    }

    /**
     * Seed expense categories.
     */
    private function seedCategories()
    {
        $categories = [
            ['name' => 'Office Supplies', 'description' => 'Stationery, paper, pens, and general office supplies', 'color' => '#3b82f6'],
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet, and phone bills', 'color' => '#eab308'],
            ['name' => 'Transportation', 'description' => 'Travel expenses, fuel, parking, and toll fees', 'color' => '#8b5cf6'],
            ['name' => 'Meals & Entertainment', 'description' => 'Staff meals, client entertainment, and refreshments', 'color' => '#f97316'],
            ['name' => 'Maintenance', 'description' => 'Office repairs, cleaning, and facility maintenance', 'color' => '#06b6d4'],
            ['name' => 'Training & Development', 'description' => 'Employee training materials and courses', 'color' => '#10b981'],
            ['name' => 'IT & Equipment', 'description' => 'Computer supplies, software, and tech equipment', 'color' => '#6366f1'],
            ['name' => 'Marketing', 'description' => 'Promotional materials and marketing expenses', 'color' => '#ec4899'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        return Category::all();
    }

    /**
     * Seed budgets for categories.
     */
    private function seedBudgets($categories)
    {
        $budgets = collect();

        foreach ($categories as $category) {
            // Current month budget
            $budgets->push(Budget::firstOrCreate(
                [
                    'category_id' => $category->id,
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                ],
                [
                    'amount' => rand(2000000, 10000000), // 2M - 10M IDR
                    'alert_threshold' => 80.00,
                ]
            ));

            // Next month budget
            $budgets->push(Budget::firstOrCreate(
                [
                    'category_id' => $category->id,
                    'start_date' => now()->addMonth()->startOfMonth(),
                    'end_date' => now()->addMonth()->endOfMonth(),
                ],
                [
                    'amount' => rand(2000000, 10000000),
                    'alert_threshold' => 80.00,
                ]
            ));
        }

        return $budgets;
    }

    /**
     * Seed cash balance periods.
     */
    private function seedCashBalances()
    {
        $balances = collect();
        $admin = User::role('admin')->first();

        // Previous month (reconciled)
        $balances->push(CashBalance::firstOrCreate(
            ['period_start' => now()->subMonth()->startOfMonth()],
            [
                'opening_balance' => 20000000,
                'closing_balance' => 18500000,
                'period_end' => now()->subMonth()->endOfMonth(),
                'reconciled_by' => $admin->id,
                'reconciliation_date' => now()->subMonth()->endOfMonth()->addDay(),
                'status' => 'reconciled',
            ]
        ));

        // Current month (active)
        $balances->push(CashBalance::firstOrCreate(
            ['period_start' => now()->startOfMonth()],
            [
                'opening_balance' => 18500000,
                'closing_balance' => null,
                'period_end' => now()->endOfMonth(),
                'status' => 'active',
            ]
        ));

        // Next month (upcoming)
        $balances->push(CashBalance::firstOrCreate(
            ['period_start' => now()->addMonth()->startOfMonth()],
            [
                'opening_balance' => 20000000,
                'closing_balance' => null,
                'period_end' => now()->addMonth()->endOfMonth(),
                'status' => 'active',
            ]
        ));

        return $balances;
    }

    /**
     * Seed transactions for balance periods.
     */
    private function seedTransactions($categories, $balancePeriods)
    {
        $transactions = collect();
        $cashier = User::role('cashier')->first();
        $admin = User::role('admin')->first();

        if (! $cashier || ! $admin) {
            $this->command->warn('⚠️  Users not found. Skipping transaction seeding.');

            return $transactions;
        }

        foreach ($balancePeriods as $period) {
            $isCurrentPeriod = $period->period_start->isCurrentMonth();
            $endDate = $isCurrentPeriod ? now() : $period->period_end;
            $daysInPeriod = $period->period_start->diffInDays($endDate);

            // Cash In transactions (3-5 per period)
            for ($i = 0; $i < rand(3, 5); $i++) {
                $date = $period->period_start->copy()->addDays(rand(0, $daysInPeriod));

                $transactions->push(Transaction::create([
                    'transaction_number' => $this->generateTransactionNumber(),
                    'type' => 'in',
                    'amount' => rand(1000000, 5000000),
                    'description' => $this->getRandomCashInDescription(),
                    'transaction_date' => $date,
                    'category_id' => null, // Cash in typically has no category
                    'user_id' => $cashier->id,
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => $date->copy()->addHours(1),
                ]));
            }

            // Cash Out transactions (10-20 per period)
            for ($i = 0; $i < rand(10, 20); $i++) {
                $date = $period->period_start->copy()->addDays(rand(0, $daysInPeriod));

                // Some transactions are pending in current period
                $isPending = $isCurrentPeriod && rand(1, 4) === 1;

                $transactions->push(Transaction::create([
                    'transaction_number' => $this->generateTransactionNumber(),
                    'type' => 'out',
                    'amount' => rand(50000, 2000000),
                    'description' => $this->getRandomCashOutDescription(),
                    'transaction_date' => $date,
                    'category_id' => $categories->random()->id,
                    'user_id' => $cashier->id,
                    'status' => $isPending ? 'pending' : 'approved',
                    'approved_by' => $isPending ? null : $admin->id,
                    'approved_at' => $isPending ? null : $date->copy()->addHours(1),
                ]));
            }
        }

        return $transactions;
    }

    /**
     * Generate unique transaction number.
     */
    private function generateTransactionNumber(): string
    {
        return 'TXN-'.now()->format('Y').'-'.str_pad(Transaction::count() + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get random cash in description.
     */
    private function getRandomCashInDescription(): string
    {
        $descriptions = [
            'Cash reimbursement from head office',
            'Petty cash top-up from main account',
            'Cash deposit from finance department',
            'Monthly petty cash allocation',
            'Budget transfer for operational expenses',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Get random cash out description.
     */
    private function getRandomCashOutDescription(): string
    {
        $descriptions = [
            'Office supplies purchase from local vendor',
            'Monthly internet and phone bills payment',
            'Staff lunch for team meeting',
            'Taxi fare for client meeting',
            'Office cleaning service - weekly',
            'Printing documents for presentation',
            'Courier service for urgent documents',
            'Light bulb replacement in meeting room',
            'Coffee and tea supplies for pantry',
            'USB flash drives for data transfer',
            'Parking fees at client office',
            'Emergency repair of office door lock',
            'Refreshments for department meeting',
            'Photocopying service for contracts',
            'Toll road fees for business trip',
            'Stationery supplies restocking',
            'Air conditioner maintenance',
            'Guest refreshments',
            'Office plants maintenance',
            'First aid kit supplies',
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
