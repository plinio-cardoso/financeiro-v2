<?php

namespace Database\Factories;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\User;
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
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'type' => fake()->randomElement([TransactionTypeEnum::Debit, TransactionTypeEnum::Credit]),
            'status' => fake()->randomElement([TransactionStatusEnum::Pending, TransactionStatusEnum::Paid]),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'paid_at' => null,
        ];
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusEnum::Pending,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the transaction is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusEnum::Paid,
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction is a debit.
     */
    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeEnum::Debit,
        ]);
    }

    /**
     * Indicate that the transaction is a credit.
     */
    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeEnum::Credit,
        ]);
    }

    /**
     * Indicate that the transaction is due today.
     */
    public function dueToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => today(),
        ]);
    }

    /**
     * Indicate that the transaction is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => TransactionStatusEnum::Pending,
            'paid_at' => null,
        ]);
    }
}
