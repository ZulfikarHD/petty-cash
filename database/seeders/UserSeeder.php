<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@pettycash.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Admin');

        // Create Accountant User
        $accountant = User::create([
            'name' => 'Accountant User',
            'email' => 'accountant@pettycash.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $accountant->assignRole('Accountant');

        // Create Cashier User
        $cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@pettycash.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $cashier->assignRole('Cashier');

        // Create Requester User
        $requester = User::create([
            'name' => 'Requester User',
            'email' => 'requester@pettycash.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $requester->assignRole('Requester');

        // Create additional test users
        User::factory()->count(5)->create()->each(function ($user) {
            // Randomly assign roles to test users
            $roles = ['Accountant', 'Cashier', 'Requester'];
            $user->assignRole($roles[array_rand($roles)]);
            $user->update(['email_verified_at' => now()]);
        });

        $this->command->info('Users created successfully!');
        $this->command->info('Default login credentials:');
        $this->command->info('  Admin: admin@pettycash.local / password');
        $this->command->info('  Accountant: accountant@pettycash.local / password');
        $this->command->info('  Cashier: cashier@pettycash.local / password');
        $this->command->info('  Requester: requester@pettycash.local / password');
    }
}
