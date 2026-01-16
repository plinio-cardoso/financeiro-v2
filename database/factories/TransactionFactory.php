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
            'user_id' => \App\Models\User::factory(),
            'title' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'type' => fake()->randomElement(['debit', 'credit']),
            'status' => fake()->randomElement(['pending', 'paid']),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'paid_at' => null,
        ];
    }
}
