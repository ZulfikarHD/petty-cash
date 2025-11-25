<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            // DemoDataSeeder::class, // Uncomment to seed demo data (categories, budgets, transactions, cash balances)
            // CashBalanceSeeder::class, // Uncomment to seed only cash balance data
        ]);
    }
}
