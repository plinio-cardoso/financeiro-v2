<?php

namespace Database\Factories;

use App\Enums\RecurringFrequencyEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringTransaction>
 */
class RecurringTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(TransactionTypeEnum::cases());
        $frequency = fake()->randomElement(RecurringFrequencyEnum::cases());
        $startDate = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'user_id' => User::factory(),
            'title' => $this->generateTitle($type),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'type' => $type,
            'frequency' => $frequency,
            'interval' => 1,
            'start_date' => $startDate,
            'end_date' => null,
            'occurrences' => null,
            'generated_count' => 0,
            'next_due_date' => $startDate,
            'active' => true,
        ];
    }

    private function generateTitle(TransactionTypeEnum $type): string
    {
        if ($type === TransactionTypeEnum::Debit) {
            return fake()->randomElement([
                'Aluguel',
                'Conta de Luz',
                'Conta de Água',
                'Internet',
                'Academia',
                'Netflix',
                'Spotify',
                'Mensalidade Escolar',
                'Plano de Saúde',
                'Condomínio',
            ]);
        }

        return fake()->randomElement([
            'Salário',
            'Freelance',
            'Aluguel Recebido',
            'Dividendos',
            'Investimentos',
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn(array $attributes) => [
            'frequency' => RecurringFrequencyEnum::Monthly,
            'interval' => 1,
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn(array $attributes) => [
            'frequency' => RecurringFrequencyEnum::Weekly,
            'interval' => 1,
        ]);
    }

    public function debit(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => TransactionTypeEnum::Debit,
            'title' => $this->generateTitle(TransactionTypeEnum::Debit),
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => TransactionTypeEnum::Credit,
            'title' => $this->generateTitle(TransactionTypeEnum::Credit),
        ]);
    }

    public function withEndDate(): static
    {
        return $this->state(fn(array $attributes) => [
            'end_date' => fake()->dateTimeBetween('+6 months', '+2 years'),
        ]);
    }

    public function withOccurrences(int $count = 12): static
    {
        return $this->state(fn(array $attributes) => [
            'occurrences' => $count,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'active' => false,
        ]);
    }
}
