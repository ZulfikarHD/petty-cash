<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+2 months');

        return [
            'category_id' => \App\Models\Category::factory(),
            'amount' => fake()->numberBetween(1000000, 50000000),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'alert_threshold' => 80.00,
        ];
    }
}
