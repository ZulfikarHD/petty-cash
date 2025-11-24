<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_number' => 'TXN-'.date('Y').'-'.str_pad(fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'type' => fake()->randomElement(['in', 'out']),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'description' => fake()->sentence(),
            'transaction_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'category_id' => null,
            'vendor_id' => null,
            'user_id' => \App\Models\User::factory(),
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the transaction is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => \App\Models\User::factory(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the transaction is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => \App\Models\User::factory(),
            'approved_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the transaction is cash in.
     */
    public function cashIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'in',
        ]);
    }

    /**
     * Indicate that the transaction is cash out.
     */
    public function cashOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'out',
        ]);
    }
}
