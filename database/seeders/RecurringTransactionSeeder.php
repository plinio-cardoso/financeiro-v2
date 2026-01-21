<?php

namespace Database\Seeders;

use App\Enums\RecurringFrequencyEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecurringTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->command->warn('No users found. Please run UserSeeder first.');

            return;
        }

        $this->command->info('Creating recurring transactions for user: '.$user->name);

        // Despesas mensais fixas
        $monthlyExpenses = [
            [
                'title' => 'Aluguel',
                'amount' => 1500.00,
                'type' => TransactionTypeEnum::Debit,
                'frequency' => RecurringFrequencyEnum::Monthly,
                'start_date' => now()->startOfMonth(),
            ],
            [
                'title' => 'Conta de Luz',
                'amount' => 250.00,
                'type' => TransactionTypeEnum::Debit,
                'frequency' => RecurringFrequencyEnum::Monthly,
                'start_date' => now()->startOfMonth()->addDays(5),
            ],
            [
                'title' => 'Internet',
                'amount' => 99.90,
                'type' => TransactionTypeEnum::Debit,
                'frequency' => RecurringFrequencyEnum::Monthly,
                'start_date' => now()->startOfMonth()->addDays(10),
            ],
            [
                'title' => 'Netflix',
                'amount' => 55.90,
                'type' => TransactionTypeEnum::Debit,
                'frequency' => RecurringFrequencyEnum::Monthly,
                'start_date' => now()->startOfMonth()->addDays(15),
            ],
            [
                'title' => 'Academia',
                'amount' => 120.00,
                'type' => TransactionTypeEnum::Debit,
                'frequency' => RecurringFrequencyEnum::Monthly,
                'start_date' => now()->startOfMonth()->addDays(1),
            ],
        ];

        foreach ($monthlyExpenses as $expense) {
            RecurringTransaction::create([
                'user_id' => $user->id,
                'title' => $expense['title'],
                'amount' => $expense['amount'],
                'type' => $expense['type'],
                'frequency' => $expense['frequency'],
                'interval' => 1,
                'start_date' => $expense['start_date'],
                'end_date' => null,
                'occurrences' => null,
                'generated_count' => 0,
                'next_due_date' => $expense['start_date'],
                'active' => true,
            ]);
        }

        // Receita mensal (salário)
        RecurringTransaction::create([
            'user_id' => $user->id,
            'title' => 'Salário',
            'amount' => 5000.00,
            'type' => TransactionTypeEnum::Credit,
            'frequency' => RecurringFrequencyEnum::Monthly,
            'interval' => 1,
            'start_date' => now()->startOfMonth()->addDays(4),
            'end_date' => null,
            'occurrences' => null,
            'generated_count' => 0,
            'next_due_date' => now()->startOfMonth()->addDays(4),
            'active' => true,
        ]);

        // Despesa semanal
        RecurringTransaction::create([
            'user_id' => $user->id,
            'title' => 'Feira Semanal',
            'amount' => 200.00,
            'type' => TransactionTypeEnum::Debit,
            'frequency' => RecurringFrequencyEnum::Weekly,
            'interval' => 1,
            'start_date' => now()->startOfWeek(),
            'end_date' => null,
            'occurrences' => null,
            'generated_count' => 0,
            'next_due_date' => now()->startOfWeek(),
            'active' => true,
        ]);

        // Despesa com data final (contrato de 12 meses)
        RecurringTransaction::create([
            'user_id' => $user->id,
            'title' => 'Financiamento do Carro',
            'amount' => 800.00,
            'type' => TransactionTypeEnum::Debit,
            'frequency' => RecurringFrequencyEnum::Monthly,
            'interval' => 1,
            'start_date' => now()->subMonths(6)->startOfMonth(),
            'end_date' => now()->addMonths(6)->endOfMonth(),
            'occurrences' => null,
            'generated_count' => 0,
            'next_due_date' => now()->startOfMonth(),
            'active' => true,
        ]);

        // Despesa com número de ocorrências
        RecurringTransaction::create([
            'user_id' => $user->id,
            'title' => 'Curso Online',
            'amount' => 150.00,
            'type' => TransactionTypeEnum::Debit,
            'frequency' => RecurringFrequencyEnum::Monthly,
            'interval' => 1,
            'start_date' => now()->startOfMonth(),
            'end_date' => null,
            'occurrences' => 6,
            'generated_count' => 0,
            'next_due_date' => now()->startOfMonth(),
            'active' => true,
        ]);

        $this->command->info('Recurring transactions created successfully!');
    }
}
