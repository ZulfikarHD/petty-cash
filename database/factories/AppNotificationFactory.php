<?php

namespace Database\Factories;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppNotification>
 */
class AppNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = AppNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['approval_request', 'approval_decision']),
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(),
            'action_url' => fake()->optional()->url(),
            'read_at' => null,
            'data' => null,
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification is an approval request.
     */
    public function approvalRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'approval_request',
            'title' => 'New Approval Request',
        ]);
    }

    /**
     * Indicate that the notification is an approval decision.
     */
    public function approvalDecision(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'approval_decision',
            'title' => fake()->randomElement(['Transaction Approved', 'Transaction Rejected']),
        ]);
    }
}
