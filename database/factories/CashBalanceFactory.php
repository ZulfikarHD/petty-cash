<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashBalance>
 */
class CashBalanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-3 months', '-1 month');
        $endDate = (clone $startDate)->modify('+1 month');

        return [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'opening_balance' => fake()->randomFloat(2, 1000, 50000),
            'closing_balance' => null,
            'notes' => fake()->optional()->sentence(),
            'reconciliation_date' => null,
            'reconciled_by' => null,
            'discrepancy_amount' => null,
            'discrepancy_notes' => null,
            'status' => 'active',
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the balance period is for the current month.
     */
    public function currentMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);
    }

    /**
     * Indicate that the balance period is for the previous month.
     */
    public function previousMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
        ]);
    }

    /**
     * Indicate that the balance is reconciled.
     */
    public function reconciled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reconciled',
            'closing_balance' => fake()->randomFloat(2, 1000, 50000),
            'reconciliation_date' => now(),
            'reconciled_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the balance is reconciled with a discrepancy.
     */
    public function withDiscrepancy(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reconciled',
            'closing_balance' => fake()->randomFloat(2, 1000, 50000),
            'reconciliation_date' => now(),
            'reconciled_by' => User::factory(),
            'discrepancy_amount' => fake()->randomFloat(2, -500, 500),
            'discrepancy_notes' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the balance is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'closing_balance' => fake()->randomFloat(2, 1000, 50000),
            'reconciliation_date' => now(),
            'reconciled_by' => User::factory(),
        ]);
    }

    /**
     * Set a specific opening balance.
     */
    public function withOpeningBalance(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'opening_balance' => $amount,
        ]);
    }
}
